<?php

namespace App\Services;

use App\Models\ProjectUnitLocator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class GoogleSheetInventoryService
{
    protected string $defaultSpreadsheetId = '14553EpRwsgN3iz5YQmc9o__Bc5ovfuRa';
    protected string $defaultGid = '149974058';

    /**
     * Tách spreadsheet ID và GID từ bất kỳ link Google Sheet nào
     */
    public static function extractSpreadsheetInfo(?string $url): array
    {
        $id = '14553EpRwsgN3iz5YQmc9o__Bc5ovfuRa';
        $gid = '149974058';

        if (!empty($url)) {
            if (preg_match('/spreadsheets\/d\/([a-zA-Z0-9-_]+)/', $url, $m)) {
                $id = $m[1];
            }
            if (preg_match('/[#&?]gid=([0-9]+)/', $url, $m)) {
                $gid = $m[1];
            }
        }

        return [
            'id' => $id,
            'gid' => $gid,
            'export_url' => "https://docs.google.com/spreadsheets/d/{$id}/export?format=xlsx",
            'view_url' => "https://docs.google.com/spreadsheets/d/{$id}/edit?gid={$gid}"
        ];
    }

    /**
     * Lấy toàn bộ bảng hàng từ Google Sheet (Có Cache)
     */
    public function getInventory(bool $forceRefresh = false, ?string $sheetName = null, ?string $customUrl = null): array
    {
        $info = self::extractSpreadsheetInfo($customUrl);
        $spreadsheetId = $info['id'];
        $defaultGid = $info['gid'];

        $cacheKey = 'google_sheet_inventory_' . $spreadsheetId . '_' . md5(($sheetName ?? 'default') . '_' . $defaultGid);

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, 180, function () use ($spreadsheetId, $defaultGid, $sheetName) {
            return $this->fetchAndParseSheet($spreadsheetId, $defaultGid, $sheetName);
        });
    }

    /**
     * Tải file XLSX từ Google Drive và bóc tách dữ liệu + màu sắc ô
     */
    protected function fetchAndParseSheet(string $spreadsheetId, string $defaultGid, ?string $requestedSheetName = null): array
    {
        $exportUrl = "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/export?format=xlsx";

        try {
            $response = Http::timeout(25)->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ])->get($exportUrl);

            if (!$response->successful()) {
                throw new \Exception("Không thể tải Google Sheet (HTTP {$response->status()})");
            }

            $tempFile = tempnam(sys_get_temp_dir(), 'gsheet_') . '.xlsx';
            file_put_contents($tempFile, $response->body());

            $zip = new ZipArchive();
            if ($zip->open($tempFile) !== true) {
                @unlink($tempFile);
                throw new \Exception("Không thể giải nén file Excel");
            }

            // 1. Đọc Shared Strings
            $sharedStrings = [];
            if ($ssContent = $zip->getFromName('xl/sharedStrings.xml')) {
                $sx = simplexml_load_string($ssContent);
                foreach ($sx->si as $si) {
                    $text = '';
                    if (isset($si->t)) {
                        $text = (string)$si->t;
                    }
                    foreach ($si->r as $r) {
                        $text .= (string)$r->t;
                    }
                    $sharedStrings[] = $text;
                }
            }

            // 2. Đọc Styles (Fills & CellXfs để lấy màu ô trạng thái)
            $stContent = $zip->getFromName('xl/styles.xml');
            $xfFill = [];
            if ($stContent) {
                $st = simplexml_load_string($stContent);
                $fills = [];
                if (isset($st->fills->fill)) {
                    foreach ($st->fills->fill as $fl) {
                        $fg = $fl->patternFill->fgColor ?? null;
                        $fills[] = $fg ? (string)($fg['rgb'] ?? ('theme' . ($fg['theme'] ?? ''))) : '';
                    }
                }
                if (isset($st->cellXfs->xf)) {
                    foreach ($st->cellXfs->xf as $xf) {
                        $xfFill[] = $fills[(int)$xf['fillId']] ?? '';
                    }
                }
            }

            // 3. Đọc Workbook (lấy danh sách các sheet)
            $sheetsMeta = [];
            if ($wbContent = $zip->getFromName('xl/workbook.xml')) {
                $wb = simplexml_load_string($wbContent);
                if (isset($wb->sheets->sheet)) {
                    foreach ($wb->sheets->sheet as $sh) {
                        $sheetsMeta[] = [
                            'name' => (string)$sh['name'],
                            'sheetId' => (string)$sh['sheetId'],
                            'rId' => (string)$sh->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'],
                            'state' => (string)($sh['state'] ?? 'visible')
                        ];
                    }
                }
            }

            // 4. Đọc Relationship để map sheetId với xml file
            $sheetFiles = [];
            if ($relsContent = $zip->getFromName('xl/_rels/workbook.xml.rels')) {
                $rels = simplexml_load_string($relsContent);
                foreach ($rels->Relationship as $rel) {
                    $sheetFiles[(string)$rel['Id']] = (string)$rel['Target'];
                }
            }

            $targetSheetFile = 'xl/worksheets/sheet2.xml';
            $targetSheetName = 'GH độc quyền tòa C2.1';
            $availableSheets = [];

            foreach ($sheetsMeta as $meta) {
                if ($meta['state'] === 'hidden') continue;
                $fileTarget = 'xl/' . ($sheetFiles[$meta['rId']] ?? '');
                $availableSheets[] = [
                    'name' => $meta['name'],
                    'file' => $fileTarget
                ];

                if ($requestedSheetName && $meta['name'] === $requestedSheetName) {
                    $targetSheetFile = $fileTarget;
                    $targetSheetName = $meta['name'];
                } elseif (!$requestedSheetName && (stripos($meta['name'], 'C2.1') !== false || stripos($meta['name'], 'độc quyền') !== false)) {
                    $targetSheetFile = $fileTarget;
                    $targetSheetName = $meta['name'];
                }
            }

            // 5. Parse dữ liệu của Target Sheet
            $parsedSheet = $this->parseWorksheet($zip, $targetSheetFile, $sharedStrings, $xfFill);
            $parsedSheet['name'] = $targetSheetName;

            $zip->close();
            @unlink($tempFile);

            return [
                'success' => true,
                'spreadsheet_id' => $spreadsheetId,
                'updated_at' => now()->format('H:i:s d/m/Y'),
                'source_url' => "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/edit?gid={$defaultGid}",
                'sheets' => $availableSheets,
                'current_sheet' => $parsedSheet
            ];

        } catch (\Throwable $e) {
            Log::error("GoogleSheetInventoryService error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi tải bảng hàng: ' . $e->getMessage(),
                'updated_at' => now()->format('H:i:s d/m/Y'),
                'source_url' => "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/edit?gid={$defaultGid}",
                'sheets' => [],
                'current_sheet' => [
                    'name' => 'Lỗi tải dữ liệu',
                    'title' => 'Chưa thể tải dữ liệu từ Google Sheet',
                    'csbh_link' => '',
                    'stats' => ['total' => 0, 'available' => 0, 'deposited' => 0, 'locked' => 0],
                    'unit_types' => [],
                    'directions' => [],
                    'units' => []
                ]
            ];
        }
    }

    /**
     * Bóc tách các dòng trong worksheet
     */
    protected function parseWorksheet(ZipArchive $zip, string $sheetFile, array $sharedStrings, array $xfFill): array
    {
        $wsContent = $zip->getFromName($sheetFile);
        if (!$wsContent) {
            throw new \Exception("Không tìm thấy worksheet: $sheetFile");
        }

        $ws = simplexml_load_string($wsContent);
        $rows = [];

        foreach ($ws->sheetData->row as $row) {
            $rIdx = (int)$row['r'];
            $cols = [];
            foreach ($row->c as $c) {
                $ref = (string)$c['r'];
                preg_match('/([A-Z]+)(\d+)/', $ref, $m);
                $colLetter = $m[1] ?? 'A';
                $val = (string)$c->v;
                $t = (string)$c['t'];
                if ($t === 's') {
                    $val = $sharedStrings[(int)$val] ?? '';
                } elseif ($t === 'inlineStr') {
                    $val = (string)$c->is->t;
                }
                $fill = $xfFill[(int)$c['s']] ?? '';
                $cols[$colLetter] = [
                    'val' => trim($val),
                    'fill' => $fill
                ];
            }
            $rows[$rIdx] = $cols;
        }

        // Bóc tách tiêu đề sheet
        $title = '';
        if (isset($rows[1])) {
            foreach ($rows[1] as $c) {
                $val = trim($c['val'] ?? '');
                if (!empty($val) && mb_strlen($val) > mb_strlen($title)) {
                    $title = $val;
                }
            }
        }
        if (empty($title)) {
            $title = 'IDC - GIỎ HÀNG ĐỘC QUYỀN THE WIN CITY';
        }
        $title = trim(str_replace(["\r", "\n"], ' ', $title));

        // Bóc tách link CSBH nếu có ở hàng 2
        $csbhLink = '';
        if (isset($rows[2])) {
            foreach ($rows[2] as $c) {
                if (preg_match('/(https?:\/\/[^\s]+)/', $c['val'] ?? '', $m)) {
                    $csbhLink = $m[1];
                    break;
                }
            }
        }

        $units = [];
        $stats = [
            'total' => 0,
            'available' => 0,
            'deposited' => 0,
            'locked' => 0
        ];

        $unitTypes = [];
        $directions = [];

        // 1. Quét tìm hàng Header (thường là hàng 4 đến 8) & lập bản đồ cột động (Dynamic Column Mapping)
        $headerRowIdx = 6;
        $colMap = [];

        for ($r = 4; $r <= 8; $r++) {
            if (!isset($rows[$r])) continue;
            $rowTexts = array_map(function($c) { return mb_strtolower($c['val'] ?? ''); }, $rows[$r]);
            $joined = implode(' ', $rowTexts);
            if (mb_strpos($joined, 'mã') !== false && (mb_strpos($joined, 'stt') !== false || mb_strpos($joined, 'loại') !== false || mb_strpos($joined, 'diện tích') !== false || mb_strpos($joined, 'tầng') !== false)) {
                $headerRowIdx = $r;
                foreach ($rows[$r] as $col => $c) {
                    $v = mb_strtolower($c['val'] ?? '');
                    if (empty($v)) continue;

                    if (mb_strpos($v, 'mã') !== false) {
                        $colMap['unit_code'] = $col;
                    } elseif (mb_strpos($v, 'loại') !== false) {
                        $colMap['unit_type'] = $col;
                    } elseif (mb_strpos($v, 'số nhà') !== false || mb_strpos($v, 'số căn') !== false) {
                        $colMap['room_number'] = $col;
                    } elseif (mb_strpos($v, 'tầng') !== false && mb_strpos($v, 'tim') === false && mb_strpos($v, 'thông') === false && mb_strpos($v, 'giá') === false) {
                        $colMap['floor'] = $col;
                    } elseif (mb_strpos($v, 'tòa') !== false || mb_strpos($v, 'tháp') !== false || mb_strpos($v, 'block') !== false) {
                        $colMap['block'] = $col;
                    } elseif (mb_strpos($v, 'thông thủy') !== false) {
                        $colMap['net_area'] = $col;
                    } elseif (mb_strpos($v, 'tim tường') !== false && mb_strpos($v, 'giá') === false && mb_strpos($v, 'đơn giá') === false) {
                        $colMap['gross_area'] = $col;
                    } elseif (mb_strpos($v, 'hướng') !== false) {
                        $colMap['direction'] = $col;
                    } elseif (mb_strpos($v, 'view') !== false) {
                        $colMap['view'] = $col;
                    } elseif ((mb_strpos($v, 'giá') !== false || mb_strpos($v, 'nvn') !== false) && mb_strpos($v, 'đơn giá') === false && mb_strpos($v, 'nước ngoài') === false && mb_strpos($v, 'nnn') === false) {
                        $colMap['price'] = $col;
                    } elseif (mb_strpos($v, 'nước ngoài') !== false || mb_strpos($v, 'nnn') !== false) {
                        $colMap['price_foreign'] = $col;
                    } elseif (mb_strpos($v, 'thanh toán') !== false || mb_strpos($v, 'pttt') !== false) {
                        $colMap['payment_method'] = $col;
                    } elseif (mb_strpos($v, 'bảng tính') !== false || mb_strpos($v, 'tham khảo') !== false) {
                        $colMap['calc_file'] = $col;
                    } elseif (mb_strpos($v, 'stt') !== false) {
                        $colMap['stt'] = $col;
                    }
                }
                break;
            }
        }

        // Các cột mặc định
        $colUnitCode = $colMap['unit_code'] ?? 'D';
        $colUnitType = $colMap['unit_type'] ?? 'E';
        $colRoomNumber = $colMap['room_number'] ?? 'F';
        $colFloor = $colMap['floor'] ?? 'G';
        $colBlock = $colMap['block'] ?? 'H';
        $colNetArea = $colMap['net_area'] ?? 'I';
        $colGrossArea = $colMap['gross_area'] ?? 'J';
        $colDirection = $colMap['direction'] ?? 'K';
        $colView = $colMap['view'] ?? 'L';
        $colPrice = $colMap['price'] ?? 'M';
        $colPriceForeign = $colMap['price_foreign'] ?? 'N';
        $colPayment = $colMap['payment_method'] ?? 'C';
        $colCalc = $colMap['calc_file'] ?? 'B';
        $colStt = $colMap['stt'] ?? 'A';

        // 2. Dữ liệu căn hộ từ sau hàng Header
        foreach ($rows as $rIdx => $cols) {
            if ($rIdx <= $headerRowIdx) continue;

            $unitCode = trim($cols[$colUnitCode]['val'] ?? '');
            // Bỏ qua dòng trống hoặc dòng mô tả không phải mã căn
            if (empty($unitCode) || mb_strlen($unitCode) < 3 || mb_strlen($unitCode) > 30 || mb_stripos($unitCode, 'thanh toán') !== false || mb_stripos($unitCode, 'tổng') !== false || mb_stripos($unitCode, 'pttt') !== false) {
                continue;
            }

            $stt = (int)($cols[$colStt]['val'] ?? count($units) + 1);
            $calcFile = $cols[$colCalc]['val'] ?? '';
            $paymentMethod = $cols[$colPayment]['val'] ?? '';
            $unitType = $cols[$colUnitType]['val'] ?? 'Căn hộ';

            // Xử lý số nhà / số căn (bỏ đuôi .0 nếu Excel lưu số)
            $roomNumber = trim($cols[$colRoomNumber]['val'] ?? '');
            if (preg_match('/^(\d+)\.0+$/', $roomNumber, $rm)) {
                $roomNumber = str_pad($rm[1], 2, '0', STR_PAD_LEFT);
            }

            // Xử lý tầng
            $floorVal = trim($cols[$colFloor]['val'] ?? '');
            $floor = (int)round((float)str_replace(',', '.', $floorVal));

            // Xử lý tòa / tháp
            $block = trim($cols[$colBlock]['val'] ?? '');
            if (empty($block) && preg_match('/^(C\d+\.\d+)/i', $unitCode, $bm)) {
                $block = strtoupper($bm[1]);
            }
            
            // Xử lý diện tích
            $netAreaStr = str_replace(',', '.', $cols[$colNetArea]['val'] ?? '0');
            $grossAreaStr = str_replace(',', '.', $cols[$colGrossArea]['val'] ?? '0');
            $netArea = round((float)$netAreaStr, 2);
            $grossArea = round((float)$grossAreaStr, 2);

            $direction = mb_strtoupper($cols[$colDirection]['val'] ?? '');
            if (empty($direction)) {
                $direction = '-';
            }
            $view = $cols[$colView]['val'] ?? '';

            // Xử lý giá tiền
            $priceVn = $this->parseNumberValue($cols[$colPrice]['val'] ?? '');
            $priceForeign = $this->parseNumberValue($cols[$colPriceForeign]['val'] ?? '');

            // Trạng thái theo màu: FFFF0000 = Đã cọc, FFFFFF00 / FFFF00 = Tạm lock, Khác = Còn hàng
            $fillD = $cols[$colUnitCode]['fill'] ?? ($cols[$colStt]['fill'] ?? ($cols['A']['fill'] ?? ''));
            
            $status = 'available';
            $statusLabel = 'Còn hàng';
            $statusColor = 'emerald';

            if (strpos($fillD, 'FFFF0000') !== false || strpos($fillD, 'FF0000') !== false) {
                $status = 'deposited';
                $statusLabel = 'Đã có cọc';
                $statusColor = 'rose';
                $stats['deposited']++;
            } elseif (strpos($fillD, 'FFFFFF00') !== false || strpos($fillD, 'FFFF00') !== false || strpos($fillD, 'FFFFF2CC') !== false) {
                $status = 'locked';
                $statusLabel = 'Tạm lock';
                $statusColor = 'amber';
                $stats['locked']++;
            } else {
                $status = 'available';
                $statusLabel = 'Còn hàng';
                $statusColor = 'emerald';
                $stats['available']++;
            }

            $stats['total']++;

            if (!in_array($unitType, $unitTypes) && !empty($unitType)) {
                $unitTypes[] = $unitType;
            }
            if (!in_array($direction, $directions) && !empty($direction) && $direction !== '-') {
                $directions[] = $direction;
            }

            $units[] = [
                'id' => $rIdx,
                'stt' => $stt,
                'unit_code' => $unitCode,
                'unit_type' => $unitType,
                'room_number' => $roomNumber,
                'floor' => $floor,
                'block' => $block,
                'area' => $netArea,
                'net_area' => $netArea,
                'gross_area' => $grossArea,
                'direction' => $direction,
                'view' => $view,
                'price' => $priceVn,
                'price_text' => $priceVn > 0 ? number_format($priceVn, 0, ',', '.') . ' đ' : 'Liên hệ',
                'price_short' => $this->formatPriceShort($priceVn),
                'price_foreign' => $priceForeign,
                'price_foreign_text' => $priceForeign > 0 ? number_format($priceForeign, 0, ',', '.') . ' đ' : 'Liên hệ',
                'payment_method' => $paymentMethod,
                'calc_file' => $calcFile,
                'status' => $status,
                'status_label' => $statusLabel,
                'status_color' => $statusColor,
            ];
        }

        return [
            'title' => $title,
            'csbh_link' => $csbhLink,
            'stats' => $stats,
            'unit_types' => $unitTypes,
            'directions' => $directions,
            'units' => $units
        ];
    }

    /**
     * Đồng bộ nạp các căn từ Google Sheet vào CSDL của dự án (project_unit_locators)
     */
    public function syncSheetUnitsToDatabase(int $projectId, string $projectName, array $units): array
    {
        $created = 0;
        $updated = 0;

        foreach ($units as $u) {
            $unitCode = trim($u['unit_code'] ?? '');
            if (empty($unitCode)) continue;

            $existing = ProjectUnitLocator::where('project_id', $projectId)
                ->where('unit_code', $unitCode)
                ->first();

            $data = [
                'project_name' => $projectName,
                'block' => $u['block'] ?? '',
                'floor' => (string)($u['floor'] ?? ''),
                'unit_axis' => (string)($u['room_number'] ?? ''),
                'unit_type' => $u['unit_type'] ?? '',
                'area' => (string)($u['net_area'] ?? ''),
                'direction' => $u['direction'] ?? '',
                'view' => $u['view'] ?? '',
                'price' => (float)($u['price'] ?? 0),
                'price_text' => $u['price_text'] ?? '',
                'status' => $u['status'] ?? 'available',
                'note' => $u['payment_method'] ?? ''
            ];

            if ($existing) {
                $existing->update($data);
                $updated++;
            } else {
                $data['project_id'] = $projectId;
                $data['unit_code'] = $unitCode;
                ProjectUnitLocator::create($data);
                $created++;
            }
        }

        return [
            'total' => count($units),
            'created' => $created,
            'updated' => $updated
        ];
    }

    /**
     * Parse số tiền từ ô excel
     */
    protected function parseNumberValue(string $val): float
    {
        $val = trim($val);
        if ($val === '') return 0;

        if (stripos($val, 'e') !== false) {
            return (float)$val;
        }

        $cleaned = str_replace(['.', ',', ' '], '', $val);
        return (float)$cleaned;
    }

    /**
     * Format giá dạng ngắn gọn (VD: 1.69 tỷ)
     */
    protected function formatPriceShort(float $price): string
    {
        if ($price <= 0) return 'Liên hệ';
        if ($price >= 1000000000) {
            return round($price / 1000000000, 3) . ' tỷ';
        }
        if ($price >= 1000000) {
            return round($price / 1000000, 1) . ' triệu';
        }
        return number_format($price, 0, ',', '.') . ' đ';
    }
}

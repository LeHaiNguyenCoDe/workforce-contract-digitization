<?php

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use App\Models\Orders;

if (!function_exists('randomFileName')) {
        function randomFileName()
        {
            $characters = '0123456789';
            $fileName = '';
            for ($i = 0; $i < 10; $i++) {
                $index = rand(0, strlen($characters) - 1);
                $fileName .= $characters[$index];
            }
            return $fileName;
        }
    }

    if (!function_exists('randomCode')) {
        function randomCode()
        {
            $characters = '0123456789';
            $randomString = '';
            for ($i = 0; $i < 8; $i++) {
                $index = rand(0, strlen($characters) - 1);
                $randomString .= $characters[$index];
            }
            return $randomString;
        }
    }

    if (!function_exists('mb_trim')) {
        function mb_trim($value, $chars = '\s　')
        {
            $value = preg_replace("/^[$chars]+/u", '', $value);
            $value = preg_replace("/[$chars]+$/u", '', $value);
            return $value;
        }
    }

if (!function_exists('isMobileDevice')) {
    function isMobileDevice($useragent)
    {
        $pattern1 = '/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i';
        $pattern2 = '/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i';

        if (preg_match($pattern1, $useragent) || preg_match($pattern2, substr($useragent, 0, 4))) {
            return 'mobile';
        }

        return '';
    }
}

if (!function_exists('getAction')) {
    function getAction($module, $action = null)
    {
        if ($action === null) {
            $action = Route::currentRouteAction() ?? 'undefine';
        }

        return $module.'_'.$action;
    }
}

    if (!function_exists('convertDateString')) {
        function convertDateString($dateString)
        {
            return Str::replace('-', '/', Str::substr($dateString, 5));
        }
    }

if (!function_exists('getRouteSupplier')) {
    function getRouteSupplier($kind, $backRoute, $idOrder)
    {
        if ($kind === 1) {
            $year = Session::get('filter_order_year');
            $month = Session::get('filter_order_month');
            $order = Orders::find($idOrder);

            if (!empty($year) && !empty($month)) {
                $url = route($backRoute, ['code' => $order->supplier_code, 'year' => $year, 'month' => $month]);
            } else {
                $url = route($backRoute, ['code' => $order->supplier_code]);
            }

            return $url;
        }

        return '#';
    }
}

if (!function_exists('getPriceMaterial')) {
    function getPriceMaterial($materialElement, $business_code)
    {
        if ($materialElement->private) {
            // Nếu cột business code trong bảng material_price_by_business bằng với code của cskd hiện tại thì lấy giá trị giá ưu đãi
            if ($materialElement->business_code_by_business === $business_code) {
                return $materialElement->price_sale_off;
            }

            // Nếu ko bằng tức là trường hợp nguyên liệu này không có cung cấp cho cskd này thì cho giá =0
            return 0;
        }

        // Ngược lại nếu không dành riêng cho cskd nào nhưng có giá ưu đãi cho cskd này thì lấy giá ưu đãi
        if ($materialElement->business_code_by_business === $business_code) {
            return $materialElement->price_sale_off;
        }

        // Ngược lại lấy giá mặc định
        return $materialElement->default_price;
    }
}

    if (!function_exists('hashFileName')) {
        function hashFileName($fileName)
        {
            $arr_part = explode('.',$fileName);
            $extension = end($arr_part);
            return randomCode().'_'.date('YdHis').'_'.time().'.'.$extension;
        }
    }

if (!function_exists('makeFolder')) {
    function makeFolder($folderUrl)
    {
        $path = 'uploads/'.$folderUrl;

        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0777, true, true);
        }

        return $path;
    }
}

    if (!function_exists('getPathFolder')) {
        function getPathFolder($folderUrl)
        {
            return public_path('uploads/'.$folderUrl);
        }
    }

    if (!function_exists('getAssetFolder')) {
        function getAssetFolder($folderUrl)
        {
            return asset('uploads/'.$folderUrl);
        }
    }

if (!function_exists('deleteFile')) {
    function deleteFile($fileUrl)
    {
        if (file_exists($fileUrl) && !is_dir($fileUrl)) {
            unlink($fileUrl);
        }
    }
}

    if (!function_exists('deleteFolder')) {
        function deleteFolder($folderUrl)
        {
            File::deleteDirectory($folderUrl);
        }
    }

if (!function_exists('moneyFormat')) {
    function moneyFormat($str)
    {
        return number_format($str, 2, '.', ',');
    }
}

    if (!function_exists('dateNow')) {
        function dateNow()
        {
            return date('Y-m-d H:i:s');
        }
    }

    if (!function_exists('dayNow')) {
        function dayNow()
        {
            return date('Y/m/d');
        }
    }

    if (!function_exists('monthInventory')) {
        function monthInventory($dateInventory)
        {
            try {
                return date("Y", strtotime ($dateInventory))." 年 ". date("m", strtotime ($dateInventory));
            } catch (\Throwable $th) {
                return '';
            }
        }
    }

    if (!function_exists('prevMonth')) {
        function prevMonth($currentMonth)
        {
            $currentMonth = $currentMonth.'-01';
            return date("Y-m", strtotime ('-1 month' , strtotime ($currentMonth )));
        }
    }

if (!function_exists('makeDirPortal')) {
    function makeDirPortal($publicUpload)
    {
        $site_name = 'main';
        $arrParams = request()->route()->parameters();

        if (!empty($arrParams['site_name'])) {
            $site_name = $arrParams['site_name'];
        }

        $path = 'uploads/'.$site_name.'/'.$publicUpload;

        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0777, true, true);
        }

        return $path;
    }
}

if (!function_exists('getPathDirPortal')) {
    function getPathDirPortal($publicUpload)
    {
        $site_name = 'main';
        $arrParams = request()->route()->parameters();

        if (!empty($arrParams['site_name'])) {
            $site_name = $arrParams['site_name'];
        }

        return public_path('uploads/'.$site_name.'/'.$publicUpload);
    }
}

if (!function_exists('pagelistLimited')) {
    function pagelistLimited($totalRows, $pageNum, $pageSize = 1, $limit = 3)
    {
        $totalRows = (int) $totalRows;
        $pageSize = (int) $pageSize;

        if ($totalRows <= 0) {
            return '';
        }

        $totalPages = ceil($totalRows / $pageSize);

        if ($totalPages <= 1) {
            return '';
        }

        $currentPage = $pageNum;

        if ($currentPage <= 0 || $currentPage > $totalPages) {
            $currentPage = 1;
        }

        $form = $currentPage - $limit;
        $to = $currentPage + $limit;

        if ($form <= 0) {
            $form = 1;
            $to = $limit * 2;
        }

        if ($to > $totalPages) {
            $to = $totalPages;
        }

        $first = '';
        $prev = '';
        $next = '';
        $last = '';
        $link = '';

        $linkUrl = url()->current();
        $queryParams = request()->query();
        unset($queryParams['page']);

        $querystring = http_build_query($queryParams);
        $sep = (!empty($querystring)) ? '&' : '';
        $linkUrl = $linkUrl.'?'.$querystring.$sep.'page=';

        if ($currentPage > $limit + 2) {
            $first = "<li class='page-item'><a href='$linkUrl' class='first page-link'>‹‹</a></li>&nbsp;";
        }

        if ($currentPage > 1) {
            $prevPage = $currentPage - 1;
            $prev = "<li class='page-item'><a href='$linkUrl$prevPage' class='prev page-link'>‹</a></li>&nbsp;";
        }

        if ($currentPage < $totalPages) {
            $nextPage = $currentPage + 1;
            $next = "<li class='page-item'><a href='$linkUrl$nextPage' class='next page-link'>›</a></li>&nbsp;";
        }

        if ($currentPage < $totalPages - 4) {
            $lastPage = $totalPages;
            $last = "<li class='page-item'><a href='$linkUrl$lastPage' class='last page-link'>››</a></li>";
        }

        for ($i = $form; $i <= $to; $i++) {
            if ($currentPage === $i) {
                $link .= "<li class='active page-item'><a class='page-link'>$i</a></li>&nbsp;";
            } else {
                $link .= "<li class='page-item'><a class='page-link' href='$linkUrl$i' class='current'>$i</a></li>&nbsp;";
            }
        }

        $pagination = '<ul class="pagination justify-content-end">'.$first.$prev.$link.$next.$last.'</ul>';

        return $pagination;
    }
}

if (!function_exists('getControllerName')) {
    function getControllerName($string)
    {
        $pieces = explode('\\', $string);
        $pieces1 = explode('@', end($pieces));

        return current($pieces1);
    }
}

if (!function_exists('formatNumber')) {
    function formatNumber($number, $type = null)
    {
        try {
            if (($type === null && is_int($number)) || $type === 'int') {
                return number_format((int) $number, 0, '', ',');
            }

            return number_format((float) $number, 2, '.', ',');
        } catch (\Throwable $th) {
            return $number;
        }
    }
}

// Build amount inventories by business_code, supplier_code, material_code key
if (!function_exists('dataAmountInventories')) {
    function dataAmountInventories($inventories)
    {
        $amountInventories = [];

        if (!empty($inventories)) {
            foreach ($inventories as $inventory) {
                $key = $inventory->business_code.'_'.$inventory->supplier_code.'_'.$inventory->material_code.'_'.$inventory->unit_price;
                $amountInventories[$key] = (float) ($inventory->amount_inventory_prev_month ?? 0);
            }
        }

        return $amountInventories;
    }
}

// Build import material by business_id, supplier_id, material_id key
if (!function_exists('dataImportMaterials')) {
    function dataImportMaterials($orders_detail)
    {
        $importMaterials = [];

        if (!empty($orders_detail)) {
            foreach ($orders_detail as $order_detail) {
                $key = $order_detail->business_code.'_'.$order_detail->supplier_code.'_'.$order_detail->material_code.'_'.$order_detail->price_import;
                $importMaterials[$key] = (float) ($order_detail->total_amount_import ?? 0);
            }
        }

        return $importMaterials;
    }
}

// Build import material by business_id, supplier_id, material_id key
if (!function_exists('materialCheckingDetailFunction')) {
    function materialCheckingDetailFunction($result_check_id)
    {
        $inspection = config('constants.result_check.inspection.value');
        $returns = config('constants.result_check.returns.value');
        $exchange = config('constants.result_check.exchange.value');

        return $result_check_id === $inspection || $result_check_id === $returns || $result_check_id === $exchange;
    }
}

if (!function_exists('checkingDetailFunction')) {
    function checkingDetailFunction($status)
    {
        // phiếu đã tạo
        return $status !== 1;
    }
}

    if(!function_exists('formatSizeUnits')){
        function formatSizeUnits($bytes)
        {
            if ($bytes >= 1073741824)
            {
                $bytes = number_format($bytes / 1073741824, 2) . ' GB';
            }
            elseif ($bytes >= 1048576)
            {
                $bytes = number_format($bytes / 1048576, 2) . ' MB';
            }
            elseif ($bytes >= 1024)
            {
                $bytes = number_format($bytes / 1024, 2) . ' KB';
            }
            elseif ($bytes > 1)
            {
                $bytes = $bytes . ' bytes';
            }
            elseif ($bytes == 1)
            {
                $bytes = $bytes . ' byte';
            }
            else
            {
                $bytes = '0 bytes';
            }

            return $bytes;
        }
    }

    if(!function_exists('getDBCheckingDetailSV')){
        function getDBCheckingDetailSV($listMaterialCheckingDetail, $checkingDetail)
        {
            if(isset($checkingDetail) && empty($checkingDetail->id)){
                return '';
            }

            $db_checking = [
                $checkingDetail->id => [// id of checkOrderId
                    'responsibility_1st_user' => $checkingDetail->check_by_1st_user_id,
                    'responsibility_2nd_user' => $checkingDetail->check_by_2nd_user_id,
                    'fee_ship' => (int) $checkingDetail->fee_ship,
                    'cmb_type_order' => $checkingDetail->type_select,
                    'date_start' => $checkingDetail->date_from_order,
                    'date_end' => $checkingDetail->date_to_order
                ]
            ];
            foreach ($listMaterialCheckingDetail as $key => $item) {
                $db_checking[$checkingDetail->id]['db_checking'][$item->id] = [//id of materialCheckingDetailId
                    'result_check' => $item->result_check_id,
                    'recive_quantity' => $item->recive_quantity,
                    'is_correct_amount' => $item->is_correct_amount,
                    'price_delivery' => $item->price_delivery,
                    'time_checking' => $item->result_date,
                    'expired_date' => $item->expired_date,
                    'temperature' => $item->temperature,
                    'is_fresh' => $item->is_fresh,
                    'check_packaging' => $item->check_packaging,
                    'is_import' => $item->is_import,
                    'note' => $item->note
                ];

            }
            return json_encode($db_checking);

        }
    }

    if (!function_exists('convJapaneseYear')) {
        function convJapaneseYear($year, $month, $day)
        {
            $name = '';
            $date = (int)sprintf('%04d%02d%02d', $year, $month, $day);
            if ($date >= 20190501) {
                if ($date <= 20191231) {
                    $name = '令和元';
                } else {
                    $name = sprintf(
                        '令和%s',
                        $year - 2018
                    );
                }
                $year = '';
            } elseif ($date >= 19890108) {
                $name = '平成';
                $year = $year - 1988;
            } elseif ($date >= 19261225) {
                $name = '昭和';
                $year = $year - 1925;
            } elseif ($date >= 19120730) {
                $name = '大正';
                $year = $year - 1911;
            } elseif ($date >= 18680125) {
                $name = '明治';
                $year = $year - 1867;
            }
            if ($year == 1) {
                return $name . '元';
            } else {
                return $name . (string)$year;
            }
        }
    }

    if (!function_exists('replaceStr')) {
        function replaceStr($str)
        {
            return mb_trim(trim(str_replace('"', '', $str)));
        }
    }

    if (!function_exists('invalidNumericInventory')) {
        function invalidNumericInventory($val)
        {
            try {
                $invalid = false;
                if(is_numeric($val) && $val < 0 || !is_numeric($val)){
                    $invalid = true;
                }
                return $invalid;
            } catch (\Throwable $e) {
                return false;
            }
        }
    }

    if (!function_exists('convertDateTimeImport')) {
        function convertDateTimeImport($date, $format = 'Y-m-d H:i:s')
        {
            $date_substr = substr($date, 0, 14);
            $d = date($format, strtotime($date_substr));
            return $d;
        }
    }

    if (!function_exists('mbConvertEncoding')) {
        function mbConvertEncoding($str)
        {
            try {
                $content_converted = mb_convert_encoding($str, "UTF-8",
                "Shift-JIS, EUC-JP, JIS, SJIS, JIS-ms, eucJP-win, SJIS-win, ISO-2022-JP,
                ISO-2022-JP-MS, SJIS-mac, SJIS-Mobile#DOCOMO, SJIS-Mobile#KDDI,
                SJIS-Mobile#SOFTBANK, UTF-8-Mobile#DOCOMO, UTF-8-Mobile#KDDI-A,
                UTF-8-Mobile#KDDI-B, UTF-8-Mobile#SOFTBANK, ISO-2022-JP-MOBILE#KDDI");
                return $content_converted;
            } catch (\Throwable $e) {
                return null;
            }
        }
    }

    if (!function_exists('findDataImportInventory')) {
        function findDataImportInventory($key, $val, $data)
        {
            try {
                $result = [];
                if(!empty($data)){
                    foreach($data as $item){
                        if($item[$key] == $val){
                            $result[] = $val;
                            break;
                        }
                    }
                }
                return $result;
            } catch (\Throwable $e) {
                return [];
            }
        }
    }

    if (!function_exists('findMaterialSupplierImportInventory')) {
        function findMaterialSupplierImportInventory($val, $data)
        {
            try {
                $result = [];
                if(!empty($data)){
                    foreach($data as $item){
                        if ($item['material_code'] .'_'. $item['supplier_code'] .'_'. $item['type_material_code'] === $val){
                            $result[] = $val;
                            break;
                        }
                    }
                }
                return $result;
            } catch (\Throwable $e) {
                return [];
            }
        }
    }

    if (!function_exists('checkIsHiraganaText')) {
        function checkIsHiraganaText($str)
        {
            if (preg_match("/^\p{Hiragana}+$/u", $str)) {
                return true;
            }
            return false;
        }
    }

    if (!function_exists('checkIsHaftWithKatakana')) {
        function checkIsHaftWithKatakana($str)
        {
            return preg_match('/^[ｧ-ﾝﾞﾟ]+$/u', $str);
        }
    }

    if (!function_exists('checkIsFullWithKatakana')) {
        function checkIsFullWithKatakana($str)
        {
            return preg_match('/^[ァ-ン]+$/u', $str);
        }
    }

    if (!function_exists('convertHiraToKata')) {
        function convertHiraToKata($str)
        {
            if(checkIsHiraganaText($str)){
                $input = $str;
                $encoding = mb_detect_encoding($input);
                if ($encoding !== 'UTF-8') {
                    $input = mb_convert_encoding($input, 'UTF-8', $encoding);
                }

                $output = mb_convert_kana($input, "KVC", 'UTF-8');
                return $output;
            }

            if(checkIsHaftWithKatakana($str)){
                return mb_convert_kana($str, "KV");
            }

            if(checkIsFullWithKatakana($str)){
                return mb_convert_kana($str, "k");
            }

            return $str;
        }
    }

    if (!function_exists('convert_lang_japanese')) {
        function convert_lang_japanese($p_src)
        {
            $arr_hankaku_daku_kanas = ['ｳﾞ', 'ｶﾞ', 'ｷﾞ', 'ｸﾞ', 'ｹﾞ', 'ｺﾞ', 'ｻﾞ', 'ｼﾞ', 'ｽﾞ', 'ｾﾞ', 'ｿﾞ', 'ﾀﾞ', 'ﾁﾞ', 'ﾂﾞ', 'ﾃﾞ', 'ﾄﾞ', 'ﾊﾞ', 'ﾋﾞ', 'ﾌﾞ', 'ﾍﾞ', 'ﾎﾞ', 'ﾊﾟ', 'ﾋﾟ', 'ﾌﾟ', 'ﾍﾟ', 'ﾎﾟ']; //Array of Half-width and Double-Byte Kana Characters
            $arr_zenkaku_daku_kanas = ['ヴ', 'ガ', 'ギ', 'グ', 'ゲ', 'ゴ', 'ザ', 'ジ', 'ズ', 'ゼ', 'ゾ', 'ダ', 'ヂ', 'ヅ', 'デ', 'ド', 'バ', 'ビ', 'ブ', 'ベ', 'ボ', 'パ', 'ピ', 'プ', 'ペ', 'ポ']; //Array of Full-width and Double-Byte Kana Characters

            $text_hankaku_kanas = "ｱｲｳｴｵｶｷｸｹｺｻｼｽｾｿﾀﾁﾂﾃﾄﾅﾆﾇﾈﾉﾊﾋﾌﾍﾎﾏﾐﾑﾒﾓﾔﾕﾖﾗﾘﾙﾚﾛﾜｦﾝｧｨｩｪｫｯｬｭｮ"; //String of Half-size Kana characters
            $text_zenkaku_hiras = "あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほまみむめもやゆよらりるれろわをんぁぃぅぇぉっゃゅょ"; //String of Full-width Hiragana characters
            $text_zenkaku_daku_hiras = "がぎぐげござじずぜぞだぢづでどばびぶべぼぱぴぷぺぽ"; //String of Full-width Dakuon Hiragana characters
            $text_hankaku_symbols = "｡｢｣､･ｰ－"; //String of Half-width Symbols, Punctuation Marks and Numbers

            $text_zenkaku_symbols = "。「」、・ー-";
            $text_zenkaku_kanas = "アイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモヤユヨラリルレロワヲンァィゥェォッャュョ"; //String of Full-width Kana Characters, Hiragana and Katakana
            $text_zenkaku_daku_kanas = "ガギグゲゴザジズゼゾダヂヅデドバビブベボパピプペポ"; //String of Full-width Dakuon Kana characters
            $text_zenkaku_space = "　"; //Full-width space characters
            $text_hankaku_space = ' ';
            $text_zenkaku_upper_alphabets = 'ＡＢＣＤＥＦＧＨＩＪＫＬＭＮＯＰＱＲＳＴＵＶＷＸＹＺ'; //String of Full-width Upper Case Alphabets
            $text_zenkaku_lower_alphabets = 'ａｂｃｄｅｆｇｈｉｊｋｌｍｎｏｐｑｒｓｔｕｖｗｘｙｚ';
            $text_hankaku_upper_alphabets = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $text_zenkaku_nums = '０１２３４５６７８９';
            $text_hankaku_nums = '0123456789';

            if (is_null($p_src) || $p_src == ''){
                return $p_src;
            }
            $text_result = $p_src;
            $arr_len = count($arr_hankaku_daku_kanas);
            for ($i = 0; $i < $arr_len; $i++) {
                $text_result = str_replace($arr_hankaku_daku_kanas[$i], $arr_zenkaku_daku_kanas[$i], $text_result );
            }

            $text_result = translate($text_result, $text_hankaku_kanas, $text_zenkaku_hiras, $text_zenkaku_daku_hiras, $text_hankaku_symbols, $text_zenkaku_space, $text_zenkaku_upper_alphabets, $text_zenkaku_lower_alphabets, $text_zenkaku_kanas, $text_zenkaku_daku_kanas, $text_zenkaku_symbols, $text_hankaku_space, $text_hankaku_upper_alphabets, $text_hankaku_nums);

            return $text_result;

        }
    }

if (!function_exists('translate')) {
    function translate($text_result, $text_hankaku_kanas, $text_zenkaku_hiras, $text_zenkaku_daku_hiras, $text_hankaku_symbols, $text_zenkaku_space, $text_zenkaku_upper_alphabets, $text_zenkaku_lower_alphabets, $text_zenkaku_kanas, $text_zenkaku_daku_kanas, $text_zenkaku_symbols, $text_hankaku_space, $text_hankaku_upper_alphabets, $text_hankaku_lower_alphabets)
    {
        $search = [
            $text_hankaku_kanas,
            $text_zenkaku_hiras,
            $text_zenkaku_daku_hiras,
            $text_hankaku_symbols,
            $text_zenkaku_space,
            $text_zenkaku_upper_alphabets,
            $text_zenkaku_lower_alphabets,
        ];

        $replace = [
            $text_zenkaku_kanas,
            $text_zenkaku_daku_kanas,
            $text_zenkaku_symbols,
            $text_hankaku_space,
            $text_hankaku_upper_alphabets,
            $text_hankaku_lower_alphabets,
        ];

        return str_replace($search, $replace, $text_result);
    }
}


    if (!function_exists('findAccountExist')) {
        function findAccountExist($val, $data)
        {
            try {
                $result = [];
                if(!empty($data)){
                    foreach($data as $item){
                        if ($item['email'] .'_'. $item['business_code'] === $val){
                            $result[] = $val;
                            break;
                        }
                    }
                }
                return $result;
            } catch (\Throwable $e) {
                return [];
            }
        }
    }

if (!function_exists('is_checked_inventory')) {
    function is_checked_inventory($check)
    {
        return (bool) $check;
    }
}

if (!function_exists('calculatorPriceMaterial')) {
    function calculatorPriceMaterial($quantity, $price_delivery)
    {
        if ($quantity !== '' && $price_delivery !== '') {
            $price = $quantity * $price_delivery;

            return config('constants.currency_sign').(int) $price;
        }

        return '';
    }
}

if (!function_exists('getFileUrl')) {
    function getFileUrl($publicUpload, $fileName)
    {
        $site_name = 'main';
        $arrParams = request()->route()->parameters();

        if (!empty($arrParams['site_name'])) {
            $site_name = $arrParams['site_name'];
        }

        return asset('uploads/'.$site_name.'/'.$publicUpload.'/'.$fileName);
    }
}

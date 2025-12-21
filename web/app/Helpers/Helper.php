<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Route;

class Helper
{

    public static function addLog($data)
    {
        if(!empty($data)){
            try {
                $data['user_id']   = auth()->user()->id ?? 0;
                $data['ip']        = request()->ip();
                /* Kiểm tra cấu hình lưu log thao tác */
                $saveActivityLog   = config('constants.save_activity_log');
                if($saveActivityLog == true && class_exists('App\Models\ActivityLog')){
                    \App\Models\ActivityLog::create($data);
                }
            } catch (\Exception $e) {
                // Log error but don't break the application
                Log::error('Failed to add activity log: ' . $e->getMessage());
            }
        }
    }

    public static function trackingError($module, $msg_log, $action = null, $channel = 'daily_errors')
    {
        if($action==null){
            $action = @Route::currentRouteAction() ?? "undefine";
        }
        
        // Group errors by module and action
        $context = [
            'module' => $module,
            'action' => $action,
            'route' => request()->fullUrl(),
            'method' => request()->method(),
            'ip' => request()->ip(),
            'user_id' => auth()->id(),
        ];
        
        Log::channel($channel)->error("[{$module}] [{$action}] {$msg_log}", $context);
    }


    public static function isDateExpiredToEdit($deliveryDate,$daysPeriod,$hoursPeriod,$minutesPeriod)
    {
        $datetimeDeliveryDate=date('Y-m-d',strtotime($deliveryDate."-".$daysPeriod." days"));
        $dateExpired=date('Y-m-d H:i',strtotime($datetimeDeliveryDate."+".$hoursPeriod." hours +".$minutesPeriod." minutes"));
        $curremtDate=date('Y-m-d H:i');
        return strtotime($curremtDate)>strtotime($dateExpired);
    }
    public static function getQueries(Builder $builder)
    {
        $addSlashes = str_replace('?', "'?'", $builder->toSql());
        return vsprintf(str_replace('?', '%s', $addSlashes), $builder->getBindings());
    }
    
    public static  function validateDate($date, $format = 'Y-m-d')
    {
        try {
            $d = DateTime::createFromFormat($format, $date);            
            return $d && $d->format($format) === $date;
        }
        catch (\Throwable $e){
            return false;
        }
    }
    
    public static function checkType($prefixName)
    {
        $typeManager            = config('constants.types_account_key.manager');
        $typeBusiness           = config('constants.types_account_key.business');

        if(Auth::check() && Auth::user()->type_account === $typeManager && strpos($prefixName,config('constants.prefix_route.manager')) !== false){
            return true;
        }
        // dd(Str::contains(Auth::user()->type_account, $typeBusiness));
        // dd($prefixName);
        if(Auth::check() && Auth::user()->type_account === $typeBusiness  && strpos($prefixName, config('constants.prefix_route.business')) !== false){
            return true;
        }
        return false;
    }

    /* convert types_account to string */
    public static function TypesAccountName($type_account)
    {
        $types_account = config('constants.types_account');
        return $types_account[$type_account] ?? null;
    }

    public static function validateDateImport($data)
    {
        try {
            $arrTemp = explode('.', $data);
            $dateTime = $arrTemp[0];
            if(strlen($dateTime) != 14 ){
                return false;
            }
            $newDate = Carbon::createFromFormat('YmdHis', $dateTime)->format('Y-m-d H:i:s');
            $check = Carbon::parse($newDate);
            return $check ? true : false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function trackingInfo($module, $msg_log, $action = null, $channel = 'track_info')
    {
        if($action==null){
            $action = @Route::currentRouteAction() ?? "undefine";
        }
        Log::channel($channel)->info($module.'_'.$action.":".$msg_log);
    }
    public static function convertEncodeStringByPassError($str,$fromCharSet,$toCharSet){
        if ($str!="" && $fromCharSet!="" && $toCharSet!=""){
            $strConvert="";
            try{
                $strConvert=iconv($fromCharSet,$toCharSet,$str);
            }
            catch (\Exception $e) {
                $arrayChar=str_split($str);
                for ($i=0;$i<count($arrayChar);$i++){
                    try{
                        $charConvert=iconv($fromCharSet,$toCharSet,$arrayChar[$i]);
                        $strConvert=$strConvert.$charConvert;
                    }
                    catch (\Exception $e){
                        $strConvert=$strConvert.$arrayChar[$i];
                    }
                }
            }
            return $strConvert;
        }
        else{
            return null;
        }
    }

    public static function getYearMonthUniqueFromData($data, $column_name){
        $arr = array_column($data, $column_name);
        $result = array_unique(array_map(function($date) {
            return DateTime::createFromFormat('Y-m-d', $date)->format('Y-m');
        }, $arr));
        return $result;
    }

    public static function infoClientLogin($params = null) {
        $info = [];
        $ip = request()->ip();
        $info['ip'] = $ip;
        $info['user_agent'] = request()->header('User-Agent');
        try {
            //$auth = Auth::user()->business_code;
            $auth = Auth::user();
            $info['user_id'] = $auth->id;
            $info['email'] = $auth->email;
        } catch (\Throwable $th) {
            //throw $th;
        }

        return json_encode($info);
    }

    public static function findInventory($params = [], $data_inventories=[]) {
        $result = [];
        if(!empty($params) && !empty($data_inventories)){
            foreach($data_inventories as $inventory){
                if(strcmp($inventory['material_code'],$params['material_code']) == 0 && strcmp($inventory['supplier_code'], $params['supplier_code']) == 0 &&  (float)$inventory['unit_price'] == (float)$params['unit_price'] &&  strcmp($inventory['year_month'], $params['year_month']) == 0 ){
                    $result = $inventory;
                    return $result;
                }
            }
        }
        return $result;
    }
}

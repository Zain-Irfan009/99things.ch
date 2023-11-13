<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
 public function Settings(){
     $shop=Auth::user();
     $setting=Setting::where('shop_id',$shop->id)->first();

     return view('admin.settings.index',compact('setting'));
 }

 public function SettingsSave(Request $request){

     $shop=Auth::user();
     $setting=Setting::where('shop_id',$shop->id)->first();
     if($setting==null){
         $setting=new Setting();
     }
     $setting->base_price_multiplier=$request->base_price_multiplier;
     $setting->base_compare_at_price_multiplier=$request->base_compare_at_price_multiplier;
     $setting->shop_id=$shop->id;
     $setting->save();
     return back()->with('success','Setting Saved Successfully');
 }
}

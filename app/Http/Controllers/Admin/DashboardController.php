<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function Dashboard(){

        $shop=Auth::user();
        $partners=Partner::count();
        $products=Product::count();
        $pending_products=Product::where('app_status',0)->count();
        $approved_products=Product::where('app_status',1)->count();
        $deny_products=Product::where('app_status',3)->count();
        $shopify_pending_products=Product::where('shopify_status','Pending')->count();
        $shopify_complete_products=Product::where('shopify_status','Complete')->count();
        $shopify_in_progress_products=Product::where('shopify_status','In-Progress')->count();
        $shopify_failed_products=Product::where('shopify_status','Failed')->count();
        return view('admin.dashboard',compact('partners','products','pending_products','approved_products','deny_products','shopify_pending_products','shopify_complete_products','shopify_in_progress_products','shopify_failed_products'));
    }
}

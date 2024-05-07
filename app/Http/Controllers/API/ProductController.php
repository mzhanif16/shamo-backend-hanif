<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function all(Request $request){
        $id = $request->input('id');
        $limit = $request->input('limit');
        $name = $request->input('name');
        $description = $request->input('description');
        $tags = $request->input('tags');
        $categories = $request->input('categories');
        $price_from = $request->input('price_from');
        $price_to = $request->input('price_to');

        if($id){
            $product = Product::with(['category','galleries'])->find($id);
            if($product){
                if($product->count() > 0) {
                    return ResponseFormatter::success(
                        $product,
                        'Data produk berhasil diambil'
                    );
                } else {
                    // Jika tidak ada data produk
                    return ResponseFormatter::success(
                        [],
                        'Tidak ada data produk'
                    );
                }
            }else{
                return ResponseFormatter::error(
                    null,
                    'Data produk tidak ada',
                    404
                );
            }
        }
        $product = Product::with(['category','galleries']);
        
        if($name){
            $product->where('name','like', '%' . $name . '%');
        }
        
        if($description){
            $product->where('name','like', '%' . $description . '%');
        }
        
        if($tags){
            $product->where('name','like', '%' . $tags . '%');
        }
        
        if($price_from){
            $product->where('price','>=',$price_from);
        }
        
        if($price_to){
            $product->where('price','<=',$price_to);
        }
        
        if($categories){
            $product->where('categories',$categories);
        }

        $result = $product->paginate($limit);

        try{
            if ($result->count() > 0) {
                return ResponseFormatter::success(
                    $result,
                    'Data list produk berhasil diambil'
                );
            } else {
                return ResponseFormatter::error(
                    [],
                    'Tidak ada data list produk',
                    400
                );
            }
        }catch (\Exception $e){
            return ResponseFormatter::error(
                null,
                'Internal Server Error',
                500
            );
        }
    
    }
}
<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function all(Request $request){
        $id = $request->input('id');
        $limit = $request->input('limit');
        $name = $request->input('name');
        $show_product = $request->input('show_product');

        if($id){
            $category = ProductCategory::with(['products'])->find($id);
            if($category){
                return ResponseFormatter::success(
                    $category,
                    'Data kategori berhasil diambil'
                );
                
            }else{
                return ResponseFormatter::error(
                    null,
                    'Data kategori tidak ada',
                    400
                );
            }
        }
        $category = ProductCategory::query();
        
        if($name){
            $category->where('name','like', '%' . $name . '%');
        }
        if($show_product){
            $category->with('products');
        }

        $result = $category->paginate($limit);

        try{
        if ($result->count() > 0) {
            return ResponseFormatter::success(
                $result,
                'Data list kategori berhasil diambil'
            );
        } else {
            return ResponseFormatter::error(
                [],
                'Tidak ada data list kategori',
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

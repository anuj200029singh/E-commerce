<?php

namespace App\Http\Controllers\admin;
use App\Models\TempImage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades;
use App\Models\Category;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\admin\TempImagesController;
use Image;

class CategoryController extends Controller
{
   
public function index(Request $request){
$categories = Category::latest();

if(!empty($request->get('keyword'))){
$categories = $categories->where('name','like','%'.$request->get('keyword').'%');

}


$categories = $categories->paginate(10);  
return view('admin.list',compact('categories'));

}

public function create(){
return view('admin.create');

}

public function store(Request $request){
$validator = Validator::make($request->all(),[
  'name'=>'required',
  'slug'=> 'required|unique:categories',
]);

if($validator->passes()){

$category = new Category() ;
$category-> name = $request->name;
$category-> slug = $request->slug;
$category-> image= $request->image; 
$category-> status = $request->status;  
$category->save();

//Save Image  Here //

if(!empty($request->image_id)){

  //Using Thisline we fetch all the Imagedata from the TempImage   //

   $tempImage = TempImage::find($request->image_id);
   //extension of image //
   $extArray = explode('.', $tempImage->name);
   $ext = last($extArray);

   $newImageName = $category->id.'.'.$ext;
   $sPath = public_path().'/temp/'.$tempImage->name;
   $dPath = public_path().'/uploads/category/'.$newImageName;
    File::copy($sPath,$dPath);
   
    //Generate Image thumbnail //
    $dPath = public_path().'/uploads/category/thumb/'.$newImageName;
    $img = Image::make($sPath );
    $img->resize(450,600);
    $img->save($dPath );

  $category->image=$newImageName;
  $category->save();
}


$request->session()->flash('success' ,'Category added Successfully');

return response()->json([
 'status'=> true,
 'message'=> 'Category added Successfully '

]);
 

} else{
return response()->json([
 'status'=> false ,
 'errors'=>$validator->errors()

]);

}


}

public function edit(){

}

public function update(){

}

public function destroy(Category $category){
 
$category->delete();

return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');

}

}

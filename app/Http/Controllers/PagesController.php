<?php
namespace App\Http\Controllers;
use App\Post;
use App\Category;
use App\User;
use Illuminate\Http\Request;
use Mail;
use Session;
use Auth;
use Image; 
use Storage;
use Cache;
use App\Http\Controllers\Admin\CategoryController;
class PagesController extends Controller{
  public function __construct(){
    $this->middleware('auth',['except' => ['getAbout','getContact','getIndex','postContact']]);
  }
  public function getIndex(){
    /*
    $posts=Post::orderBy('fixed','desc')->orderBy('id','desc')->paginate(9);
    $category=Category::all();
    $this->site_settings = "ad";
    return view("pages.welcome")->withPosts($posts)->withCategory($category);
    */
    $blogController = new Admin\BlogController;
    print $blogController->getIndex();
  }
  public function getAbout(){
    $user=User::find("1");
    return view(tema().".about")->withData($user);
  }
  public function getContact(){
    return view(tema().".contact");
  }
  public function getProfile(){
    return view("admin.profile");
  }
  public function saveProfile(Request $request){
    $this->validate($request,array(
      'name'    => 'required|min:3|max:255',
      'email'   => 'required|min:3|max:255'
    ));
    $user=User::find(Auth::user()->id);
    $user->name=$request->name;
    $user->email=$request->email;
    $user->about=$request->about;
    if ($request->hasFile('img')) {
      $slug=self_url(($request->name));
      $img=$request->file('img');
      $filename=$slug.".".$img->getClientOriginalExtension();
      $location=public_path('images/'.$filename);
      $img=Image::make($img)->save($location);
      $oldfilename=$user->picture;
      $user->picture=$filename;
    }
    $user->save();
    Session::flash('success','Profil Güncellendi.'); 
    return redirect()->route(tema().'.login.index');
  }
  public function postContact(Request $request){
    $this->validate($request,array(
      'subject' => 'required|min:3|max:255',
      'email'   => 'required|min:3|max:255', 
      'bodyMessage' => 'required|min:10'
    ));
    $data=array(
      'subject' =>$request->subject ,
      'email' =>$request->email ,
      'bodyMessage' =>$request->bodyMessage
    );
    Mail::send('admin.emails.contact',$data,function($message)use ($data){
      $message->from($data['email']);
      $message->to('anil@bilgimedya.com.tr');
      $message->subject($data['subject']);
    });
    Session::flash('success','Mesaj Gönderildi');
    return redirect('contact');
  }
}
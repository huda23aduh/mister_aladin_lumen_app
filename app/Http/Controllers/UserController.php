<?php



namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

use App\User;
use App\Tb_fetch_image;
use Illuminate\Http\Request;

class UserController extends Controller
{

    public function showAllAuthors()
    {
        return response()->json(User::all());
    }

    public function fetchFunction()
    {
        $uri = 'https://randomfox.ca/floof/';

        $json_var = $this->sendRequest($uri);

        $var_original_image_url = json_decode($json_var)->image;
        $var_link = json_decode($json_var)->link;

        
        $the_image_data = new Tb_fetch_image;
        $the_image_data->original_image_url = $var_original_image_url;
        $the_image_data->link = $var_link;
        $the_image_data->save();

        $path_parts = pathinfo($var_original_image_url);
        $the_image_basename = $path_parts['basename'];

        $this->download_image1($var_original_image_url, $the_image_basename);

        return $json_var;
    }

    public function download_image1($image_url, $image_file){
        $fp = fopen ($image_file, 'w+');              

        $ch = curl_init($image_url);

        curl_setopt($ch, CURLOPT_FILE, $fp);          
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1000);      
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');

        curl_exec($ch);

        curl_close($ch);                              
        fclose($fp);                                  
    }

    public function sendRequest($uri){
        $curl = curl_init($uri);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public function showOneAuthor($id)
    {
        return response()->json(User::find($id));
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'phone' => 'required|numeric'
        ]);

        $user = User::create($request->all());

        return response()->json($user, 201);
    }

    public function update($id, Request $request)
    {
        $user = User::findOrFail($id);
        $user->update($request->all());

        return response()->json($user, 200);
    }

    public function delete($id)
    {
        User::findOrFail($id)->delete();
        return response('Deleted Successfully', 200);
    }
}
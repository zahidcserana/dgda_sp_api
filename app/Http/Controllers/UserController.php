<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function showAllUsers()
    {
        return response()->json(User::all());
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'user_name' => 'required',
            'user_email' => 'required|email|unique:users',
            'user_mobile' => 'required',
            'password' => 'required|confirmed|min:6',
        ]);
        $data = $request->all();
        $userModel = new User();
        $user = $userModel->create($data);

        return response()->json($user);
    }

    public function verifyUser(Request $request)
    {
        $this->validate($request, [
            'verification_pin' => 'required',
            'user_mobile' => 'required',
        ]);

        $user = DB::table('users')
            ->where('user_mobile', $request->user_mobile)
            ->where('verification_pin', $request->verification_pin)
            ->first();

        return response()->json($user);

    }

    public function getVerificationCode(Request $request)
    {
        $this->validate($request, [
            'user_mobile' => 'required',
        ]);

        $user = DB::table('users')
            ->where('user_mobile', $request->user_mobile)
            ->value('verification_pin');

        return response()->json($user);

    }

    /*
public function showOneAuthor($id)
    {
        return response()->json(Author::find($id));
    }



    public function update($id, Request $request)
    {
        $author = Author::findOrFail($id);
        $author->update($request->all());

        return response()->json($author, 200);
    }

    public function delete($id)
    {
        Author::findOrFail($id)->delete();
        return response('Deleted Successfully', 200);
    }
    */

}

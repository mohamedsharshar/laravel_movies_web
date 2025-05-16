<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        $usersCount = User::count();
        $contactsCount = DB::table('contact_messages')->count();
        return view('admin.dashboard', compact('usersCount', 'contactsCount'));
    }

    public function users()
    {
        $users = User::all();
        return view('admin.users', compact('users'));
    }

    public function deleteUser($id)
    {
        if ($id == auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }
        User::findOrFail($id)->delete();
        return back()->with('success', 'User deleted successfully.');
    }

    public function contacts()
    {
        $contacts = DB::table('contact_messages')->orderByDesc('id')->get();
        return view('admin.contacts', compact('contacts'));
    }

    public function deleteContact($id)
    {
        DB::table('contact_messages')->where('id', $id)->delete();
        return back()->with('success', 'Contact message deleted.');
    }
}

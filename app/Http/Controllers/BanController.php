<?php

namespace App\Http\Controllers;

use App\Models\Ban;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class BanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bans = Ban::all();
        if (is_null($bans)) {
            return response()->json(['error' => 'Bans not found'], 404);
        }
        Ban::paginate(10);
        return response()->json(['bans' => $bans], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $ban = Ban::create($request -> input());
        return response()->json(['message' => 'Ban created!', 'ban' => $ban], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $ban = Ban::find($id);
        if (is_null($ban)) {
            return response()->json(['error' => 'Ban not found'], 404);
        }
        return response()->json(['ban' => $ban]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        $ban = Ban::find($id);
        if (is_null($ban)) {
            return response()->json(['error' => 'Ban not found'], 404);
        }
        $ban->update($request -> input());
        return response()->json(['message' => 'Ban updated!', 'ban' => $ban], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $ban = Ban::find($id);
        if (is_null($ban)) {
            return response()->json(['error' => 'Ban not found'], 404);
        }
        $ban->delete();
        return response()->json(['message' => 'Ban deleted']);
    }
}

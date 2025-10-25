<?php

namespace App\Http\Controllers;

use App\Models\Pos;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function index()
    {
        $posList = Pos::orderBy('pos_name')->get();
        return view('pos.index', compact('posList'));
    }

    public function create()
    {
        return view('pos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'pos_name' => 'required|string|max:100',
            'pos_description' => 'nullable|string|max:100',
        ]);
        Pos::create($request->only(['pos_name', 'pos_description']));
        return redirect()->route('pos.index')->with('success', 'Pos Pembayaran berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $pos = Pos::findOrFail($id);
        return view('pos.edit', compact('pos'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'pos_name' => 'required|string|max:100',
            'pos_description' => 'nullable|string|max:100',
        ]);
        $pos = Pos::findOrFail($id);
        $pos->update($request->only(['pos_name', 'pos_description']));
        return redirect()->route('pos.index')->with('success', 'Pos Pembayaran berhasil diupdate!');
    }

    public function destroy($id)
    {
        $pos = Pos::findOrFail($id);
        $pos->delete();
        return redirect()->route('pos.index')->with('success', 'Pos Pembayaran berhasil dihapus!');
    }
} 
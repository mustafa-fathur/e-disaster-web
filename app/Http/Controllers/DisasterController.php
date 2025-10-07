<?php

namespace App\Http\Controllers;

use App\Models\Disaster;
use App\Enums\DisasterTypeEnum;
use App\Enums\DisasterStatusEnum;
use App\Enums\DisasterSourceEnum;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DisasterController extends Controller
{
    public function index(): View
    {
        $disasters = Disaster::query()
            ->latest()
            ->paginate(15);

        return view('admin.disasters', compact('disasters'));
    }

    public function create(): View
    {
        return view('admin.disasters-create', [
            'types' => DisasterTypeEnum::cases(),
            'statuses' => DisasterStatusEnum::cases(),
            'sources' => DisasterSourceEnum::cases(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'source' => 'required|in:BMKG,manual',
            'types' => 'required|in:gempa bumi,tsunami,gunung meletus,banjir,kekeringan,angin topan,tahan longsor,bencanan non alam,bencana sosial',
            'status' => 'required|in:ongoing,completed',
            'date' => 'nullable|date',
            'time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'coordinate' => 'nullable|string',
            'lat' => 'nullable|numeric',
            'long' => 'nullable|numeric',
            'magnitude' => 'nullable|numeric',
            'depth' => 'nullable|numeric',
        ]);

        $data['reported_by'] = auth()->id();
        Disaster::create($data);

        return redirect()->route('admin.disasters')->with('success', 'Disaster created.');
    }

    public function show(Disaster $disaster): View
    {
        return view('admin.disasters-show', compact('disaster'));
    }

    public function edit(Disaster $disaster): View
    {
        return view('admin.disasters-edit', [
            'disaster' => $disaster,
            'types' => DisasterTypeEnum::cases(),
            'statuses' => DisasterStatusEnum::cases(),
            'sources' => DisasterSourceEnum::cases(),
        ]);
    }

    public function update(Request $request, Disaster $disaster)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'source' => 'required|in:BMKG,manual',
            'types' => 'required|in:gempa bumi,tsunami,gunung meletus,banjir,kekeringan,angin topan,tahan longsor,bencanan non alam,bencana sosial',
            'status' => 'required|in:ongoing,completed',
            'date' => 'nullable|date',
            'time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'coordinate' => 'nullable|string',
            'lat' => 'nullable|numeric',
            'long' => 'nullable|numeric',
            'magnitude' => 'nullable|numeric',
            'depth' => 'nullable|numeric',
        ]);

        $disaster->update($data);

        return redirect()->route('admin.disasters')->with('success', 'Disaster updated.');
    }

    public function destroy(Disaster $disaster)
    {
        $disaster->delete();
        return redirect()->route('admin.disasters')->with('success', 'Disaster deleted.');
    }
}



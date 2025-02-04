<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WorkspaceController extends Controller
{
    public function list()
    {
        $workspaces = Workspace::all();
        $workspaces = $workspaces->toArray();

        return $this->api_response_success('Workspace berhasil diambil', $workspaces);
    }

    public function getDetail(Request $request)
    {
        $workspaces = Workspace::find($request->id);
        $workspaces = $workspaces->toArray();

        return $this->api_response_success('Workspace berhasil diambil', $workspaces);
    }

    public function saveInformasiWorkspace(Request $request)
    {
        // Manually create the validator and validate the request
        $validator = \Validator::make($request->all(), [
            'nama_workspace' => 'nullable|string|max:255',
            'id' => 'nullable|exists:workspaces,id', // Ensure 'id' exists in the workspaces table if provided
        ]);

        // If validation fails, return the errors
        if ($validator->fails()) {
            return $this->api_response_error('Validation gagal disimpan', $validator->errors()->all(), $validator->errors()->keys());
        }

        // If no 'id' is provided, create a new workspace
        if ($request->id == null) {
            $workspace = new Workspace();
            $workspace->urutan_status_workspace = 1;
        } else {
            // If 'id' is provided, find the workspace to update
            $workspace = Workspace::find($request->id);

            // Check if the workspace exists before updating
            if (!$workspace) {
                return $this->api_response_error('Workspace not found', 404);
            }

            if($workspace->urutan_status_workspace == 1) $workspace->urutan_status_workspace = 2;
        }

        // Set the data for the workspace (whether new or updated)
        $workspace->nama_workspace = $request->nama_workspace;

        // Save the workspace (create or update)
        $workspace->save();

        // Return the success response with the workspace data
        return $this->api_response_success('Workspace berhasil disimpan', $workspace->toArray());
    }

    private function calculateSquareAreaMin($coordinates)
    {
        // Step 3: Initialize min and max values for X and Y
        $minX = $maxX = $coordinates['kiri_atas']['x'];
        $minY = $maxY = $coordinates['kiri_atas']['y'];

        // Step 4: Find the min/max X and Y values
        $minX = min($minX, $coordinates['kanan_atas']['x'], $coordinates['kiri_bawah']['x'], $coordinates['kanan_bawah']['x']);
        $maxX = max($maxX, $coordinates['kanan_atas']['x'], $coordinates['kiri_bawah']['x'], $coordinates['kanan_bawah']['x']);

        $minY = min($minY, $coordinates['kiri_atas']['y'], $coordinates['kanan_atas']['y'], $coordinates['kiri_bawah']['y'], $coordinates['kanan_bawah']['y']);
        $maxY = max($maxY, $coordinates['kiri_atas']['y'], $coordinates['kanan_atas']['y'], $coordinates['kiri_bawah']['y'], $coordinates['kanan_bawah']['y']);

        // Step 5: Calculate width and height of the bounding box
        $width = $maxX - $minX;
        $height = $maxY - $minY;

        // Step 6: Find the side length of the square (max of width and height)
        $sideLength = max($width, $height);

        // Step 7: Calculate the area of the square
        $area = $sideLength * $sideLength;

        return $area;
    }

    public function saveTitikKoordinatWorkspace(Request $request)
    {
        // Coordinates positions to validate
        $positionsOfCoordinate = ['kiri_atas', 'kanan_atas', 'kiri_bawah', 'kanan_bawah'];

        // Validation rules
        $rules = [
            'titik_koordinat' => 'required|array',
        ];

        // Loop to dynamically add rules for each coordinate position
        foreach ($positionsOfCoordinate as $position) {
            $rules["titik_koordinat.$position"] = 'required|array|size:2';
            $rules["titik_koordinat.$position.x"] = 'required|numeric';
            $rules["titik_koordinat.$position.y"] = 'required|numeric';
        }

        // Validate the request data
        $request->validate($rules);

        // Calculate the area of the square (assuming this function is defined somewhere)
        $luas_persegi = $this->calculateSquareAreaMin($request->titik_koordinat);

        // Find the workspace by ID
        $workspace = Workspace::find($request->id);

        // Check if workspace exists
        if (!$workspace) {
            return $this->api_response_error('Workspace not found', [], [], 404);
        }

        if($workspace->urutan_status_workspace == 2) $workspace->urutan_status_workspace = 3;

        // Update workspace fields
        $workspace->titik_koordinat = $request->titik_koordinat;
        $workspace->luas_persegi = $luas_persegi;

        // Save the workspace (create or update)
        $workspace->save();
        $workspace->refresh();

        // Return the success response with the updated workspace
        return $this->api_response_success('Workspace berhasil disimpan', $workspace->toArray());
    }

    public function savePohon(Request $request)
    {
        // Manually create the validator and validate the request
        $validator = \Validator::make($request->all(), [
            // 'foto' => 'required|',
            'foto' => 'required|image|max:51200',
            'path_foto' => 'nullable|string',
            'id' => 'required|exists:workspaces,id',
        ]);

        // If validation fails, return the errors
        if ($validator->fails()) {
            return $this->api_response_error('Validation gagal disimpan', $validator->errors()->all(), $validator->errors()->keys());
        }

        // Find the workspace by ID
        $workspace = Workspace::find($request->id);

        // Check if workspace exists
        if (!$workspace) {
            return $this->api_response_error('Workspace not found', [], [], 404);
        }

        $key_got = false;

        if($request->path_foto) {
            Storage::delete($request->path_foto);
            $key_got = array_search($request->path_foto, array_column($workspace->pohon, 'path_foto'));
        }

        $path_foto = $request->file('foto')->store('uploads/pohon', 'public');

        $pohon = $workspace->pohon ?? [];
        if($key_got != false) {
            $pohon[$key_got] = [
                "path_foto" => $path_foto,
                "nama_spesies" => $pohon[$key_got]["nama_spesies"],
                // "nama_spesies" => "berubah",
                "dbh" => $pohon[$key_got]["dbh"],
            ];
        } else {
            // Append the new data to the array
            $pohon[] = [
                "path_foto" => $path_foto,
                "nama_spesies" => "dummy",
                "dbh" => 0,
            ];
        }
        // Reassign the modified array back to the model
        $workspace->pohon = $pohon;


        // Save the workspace (create or update)
        $workspace->save();
        $workspace->refresh();

        return $this->api_response_success('Pohon berhasil digenerate', $workspace->toArray());
    }

    private function generateShanonWannerTable($listPohon)
    {
        // $listPohon = [
        //     'Species A' => 10,
        //     'Species B' => 20,
        //     'Species C' => 15,
        //     'Species D' => 5
        // ];

        // Total number of trees
        $totalPohon = array_sum($listPohon);

        // Initialize Shannon-Wiener Index components
        $shannonIndex = 0;
        $shannonTable = [];

        // Compute proportions and entropy for each species
        foreach ($listPohon as $spesies => $frekuensi) {
            $proporsi = $frekuensi / $totalPohon;
            $keragaman = $proporsi * log($proporsi);  // p_i * ln(p_i)
            $shannonIndex += $keragaman;

            // Save the data for the table
            $shannonTable[] = [
                'spesies' => $spesies,
                'frekuensi' => $frekuensi,
                'proporsi' => $proporsi,
                'keragaman' => $keragaman
            ];
        }

        // Shannon-Wiener Index is the negative sum of entropy terms
        $shannonIndex = -$shannonIndex;

        return [
            "shannon_table" => $shannonTable,
            "shannon_index" => $shannonIndex,
        ];
    }

    private function mappingPohonFreq($listPohon)
    {
        $listBaru = [];

        foreach($listPohon as $pohon) {
            $spesies = $pohon['nama_spesies'];

            // Increment the count for the species, or initialize it if not set
            if (!isset($listBaru[$spesies])) {
                $listBaru[$spesies] = 0;
            }

            $listBaru[$spesies]++;
        }

        return $listBaru;
    }

    public function saveFinalResult(Request $request)
    {
        // Manually create the validator and validate the request
        $validator = \Validator::make($request->all(), [
            'id' => 'required|exists:workspaces,id',
            'pohon' => 'required|array',
        ]);

        // If validation fails, return the errors
        if ($validator->fails()) {
            return $this->api_response_error('Validation gagal disimpan', $validator->errors()->all(), $validator->errors()->keys());
        }

        $pohonPerSpesies = $this->mappingPohonFreq($request->pohon);
        // return $pohonPerSpesies;
        $shannonWanner = $this->generateShanonWannerTable($pohonPerSpesies);

        // Find the workspace by ID
        $workspace = Workspace::find($request->id);
        $workspace->hasil_akhir = $shannonWanner;
        if($workspace->urutan_status_workspace == 3) $workspace->urutan_status_workspace = 4;

        // Save the workspace (create or update)
        $workspace->save();
        $workspace->refresh();

        return $this->api_response_success('Pohon berhasil digenerate', $workspace->toArray());
    }
}

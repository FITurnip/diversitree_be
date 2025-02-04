<?php

namespace App\Http\Controllers;

use App\Models\StatusWorkspace;
use Illuminate\Http\Request;

class StatusWorkspaceController extends Controller
{
    public function list()
    {
        $statusWorkspaces = StatusWorkspace::all();
        $statusWorkspaces = $statusWorkspaces->toArray();

        return $this->api_response_success('Status Workspace berhasil diambil', $statusWorkspaces);
    }
}

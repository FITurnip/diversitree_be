<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusWorkspaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \DB::table('status_workspaces')->insert([
            [
                "urutan" => 1,
                "nama_status" => "Inisiasi Workspace",
            ],
            [
                "urutan" => 2,
                "nama_status" => "Menentukan Area",
            ],
            [
                "urutan" => 3,
                "nama_status" => "Pemotretan Pohon",
            ],
            [
                "urutan" => 4,
                "nama_status" => "Table Shannon Wanner",
            ],
        ]);
    }
}

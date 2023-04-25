<?php

namespace App\Models\Advertisements;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvTypologyMstr extends Model
{
    use HasFactory;

    public function listTypology()
    {
        $typology = AdvTypologyMstr::where('status', '1')
            ->select(
                'id',
                'type',
                'type_inner as subtype',
                'descriptions'
            )
            ->orderBy('type_inner')
            ->get();

        $typologyList = $typology->groupBy('type');
        foreach ($typologyList as $key => $data) {
            $type = [
                'Type' => "Type " . $key,
                'data' => $typologyList[$key]
            ];
            $fData[] = $type;
        }
        return $fData;
    }

    public function getHordingCategory()
    {
        $typology = AdvTypologyMstr::where('status', '1')
            ->select(
                'id',
                'type_inner as subtype',
                'descriptions'
            )
            ->orderBy('type_inner')
            ->get();

        return $typology;
    }

    public function listTypology1()
    {
        $typology = AdvTypologyMstr::where('status', '1')
            ->select(
                'id',
                'type',
                'type_inner as subtype',
                'descriptions'
            )
            ->orderBy('type_inner')
            ->get();

        return  $typology;
    }
}

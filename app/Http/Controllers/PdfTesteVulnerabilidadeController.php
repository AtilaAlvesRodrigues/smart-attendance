<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class PdfTesteVulnerabilidadeController extends Controller
{
    public function index()
    {
        return view('pdf_teste_vulnerabilidade');
    }

    public function imprimir()
    {
        return view('pdf_teste_vulnerabilidade');
    }
}

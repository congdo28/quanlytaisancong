<?php

namespace App\Http\Controllers;

use App\Models\Documents;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\IOFactory;


class MaubaocaoController extends Controller
{
    
    public $documents ;

    public function __construct()
    {
        $this->documents = new Documents;
    }

    public function index(){
        $data = $this->documents->paginate(8);
        return view('layout.Maubaocao',['baocao'=>$data]);
    }

    public function file_view($name){
        $name =substr($name,0,strpos($name,'.docx'));
        return view('layout.viewpdf',['name'=>$name]);
    }

    public function word_export($id){
        $template = new TemplateProcessor('word/'.$id);
        $template->saveAs('maubaocao'.'.docx');
        return response()->download('maubaocao'.'.docx')->deleteFileAfterSend(true);
    }   

    

    public function store(Request $request ){
        $domPdfPath = base_path( 'vendor/dompdf/dompdf');
            \PhpOffice\PhpWord\Settings::setPdfRendererPath($domPdfPath);
            \PhpOffice\PhpWord\Settings::setPdfRendererName('DomPDF');
        $file = $request->file_temp;
        $phpWord =IOFactory::createReader('Word2007')->load($request->file('file_temp')->path());
        $objWriter = IOFactory::createWriter($phpWord,'PDF');
        $objWriter->save('PDF/'.substr($file->getClientOriginalName(),0,strpos($file->getClientOriginalName(),'.docx')).'.pdf');
        $document = new Documents;
        $document->tieude=$request->tieude;
        $document->noidung = $request->noidung;
        $document->name_file = $file->getClientOriginalName();
        $document->save();
        $file->move('word',$file->getClientOriginalName());
        return redirect('/maubaocao');
      
    }

    public function search(){
        $data = $this->documents->simplePaginate(8);
        if(isset($_POST['text']) && $_POST['text'] !=''){
            $text = $_POST['text'];
            $data = $this->documents->where(function($res) use($text){
                $res->where('tieude','like','%'.$text.'%')
                    ->orwhere('noidung','like','%'.$text.'%');
            })->simplePaginate(8);
        }
        $output = '';
        if(!empty($data)){
            foreach($data as $val){
                $output .='
                <tr>
                <td style="border: 1px solid rgba(0,0,0,.1)">'.$val->id.'</td>
                <td style="border: 1px solid rgba(0,0,0,.1)">'.$val->tieude.'</td>
                <td style="border: 1px solid rgba(0,0,0,.1)">'.$val->noidung.'</td>
                <td style="display: flex; align-items: center;justify-content: center" >
                    <a href="'.url('/maubaocao/'.$val->name_file.'/view').'"  class="btn_view"><i class="bx bx-show-alt" style="font-size: 30px; color:rgb(90, 210, 250); font-weight: bold; "></i></a>
                    <a href="'.url('/word_export',$val->name_file).'"  class="btn_download" ><i class="bx bx-download" style="font-size: 30px; color:rgb(255, 31, 31); font-weight: bold;"></i></a>
                </td>
            </tr>
                ';
            }
        }
        echo $output ;
    }
}

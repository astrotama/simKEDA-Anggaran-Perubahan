<?php
function kegiatanrevisi_pick_main($arg=NULL, $nama=NULL) {
	drupal_add_css('files/css/kegiatancam.css');
 	drupal_add_js('files/js/kegiatanlt.js');

	$id = arg(2);	
	$kodeuk = arg(3);	


	drupal_set_title('Pilih Kegiatan');
	
	$header = array (
		array('data' => '', 'width' => '5px'),
		array('data' => 'No', 'width' => '10px'),
		array('data' => 'Kegiatan', 'field'=> 'kegiatan'),
		array('data' => 'Anggaran', 'field'=> 'totalp'),
		//array('data' => 'Penundaan'),
		array('data' => 'Plafon', 'field'=> 'plafon'),
		array('data' => 'Sumberdana', 'field'=> 'sumberdana1'),
		array('data' => '', 'width' => '90px'),
	);
		
	$tablesort = tablesort_sql($header);
    if ($tablesort=='') {
        $tablesort=' order by kegiatan';
    }

	$customwhere = " and kodeuk='" . $kodeuk . "' ";
    //$where = ' where totalp>0 and kodekeg not in (select kodekeg from {kegiatanrevisi}) ' . $customwhere ;
	$where = ' where inaktif=0 and bintang=0 and kodekeg not in (select kodekeg from {kegiatanrevisi}) ' . $customwhere ;

    $sql = 'select kodekeg,kegiatan,totalp,anggaran,plafon,sumberdana1,inaktif,bintang from {kegiatanperubahan} ' . $where;
    $fsql = sprintf($sql, addslashes($nama));
	
	//drupal_set_message($sql);
	
    $limit = 30;
    $countsql = "select count(*) as cnt from {kegiatanperubahan} " . $where;
    $fcountsql = sprintf($countsql, addslashes($nama));
    $result = pager_query($fsql . $tablesort, $limit, 0, $fcountsql);
    
    $no=0;
    $page = $_GET['page'];
    if (isset($page)) {
        $no = $page * $limit;
    } else {
        $no = 0;
    }
    if ($result) {
        while ($data = db_fetch_object($result)) {

			$kegname = l($data->kegiatan, 'apbd/kegiatanrevisi/editperubahan/0/' . $kodeuk . '/' . $data->kodekeg, array('html' =>TRUE));
			$editlink = l('Usulkan', 'apbd/kegiatanrevisi/editperubahan/0/' . $kodeuk . '/' . $data->kodekeg, array('html' =>TRUE));
			
			$penundaaan = ($data->totalp>$data->anggaran ? $data->totalp-$data->anggaran : 0);
			if ($data->inaktif=='1') {
				$str_info = "<img src='/files/inaktif.png'>";
				$str_keg = " (Tidak Aktif)";
			} elseif ($data->bintang=='0') {
				$str_info = "";
				
			} else {
				$str_info = "<img src='/files/bintang.png'>";
				$str_keg = " (Dibintang)";
			}
            $no++;
            $rows[] = array (
				array('data' => $str_info, 'align' => 'right'),
                array('data' => $no, 'align' => 'right'),
                array('data' => $kegname . $str_keg, 'align' => 'left'),
                array('data' => apbd_fn($data->totalp), 'align' => 'right'),
				//array('data' => apbd_fn($penundaaan), 'align' => 'right'),
				array('data' => apbd_fn($data->plafon), 'align' => 'right'),
                array('data' => $data->sumberdana1, 'align' => 'left'),
                array('data' => $editlink, 'align' => 'right'),
            );
        }
    } else {
        $rows[] = array (
            array('data' => 'Data kosong, tidak bisa menambahkan perubahan', 'colspan'=>'3')
        );
    }
	
	$output = theme_box('', theme_table($header, $rows));

	
    $output .= theme ('pager', NULL, $limit, 0);
    return $output;
}

function kegiatanrevisi_pick_filter_form() {

}

function kegiatanrevisi_pick_filter_form_submit($form, &$form_state) {
}
?>
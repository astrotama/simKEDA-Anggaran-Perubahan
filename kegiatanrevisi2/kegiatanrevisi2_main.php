<?php
function kegiatanrevisi2_main($arg=NULL, $nama=NULL) {
	drupal_add_css('files/css/kegiatancam.css');
 	drupal_add_js('files/js/kegiatanlt.js');

	$header = array (
		array('data' => 'No', 'width' => '10px'),
		array('data' => 'Kegiatan', 'field'=> 'kegiatan', 'width' => '50px'),
		array('data' => 'Anggaran', 'field'=> 'totalp'),
		array('data' => 'Sumber Dana', 'field'=> 'sumberdana1'),
		array('data' => '', 'width' => '90px'),
	);
		
	$tablesort = tablesort_sql($header);
    if ($tablesort=='') {
        $tablesort=' order by kegiatan';
    }

	if (isSuperuser())
		$kodeuk = '81';
	else
		$kodeuk = apbd_getuseruk();
	
	$customwhere = " and kodeuk='" . $kodeuk . "' ";
    $where = ' where kodekeg not in (select kodekeg from {kegiatanrevisiperubahan}) ' . $customwhere ;

    $sql = 'select kodekeg,kegiatan,totalp,sumberdana1,inaktif from {kegiatanperubahan} ' . $where;
    $fsql = sprintf($sql, addslashes($nama));
	
	drupal_set_message($sql);
	
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
			$editlink = l('Usulkan Perubahan', 'apbd/kegiatanrevisi/editperubahan/0/' . $kodeuk . '/' . $data->kodekeg, array('html' =>TRUE));
		
            $no++;
            $rows[] = array (
                array('data' => $no, 'align' => 'right'),
                array('data' => $kegiatan, 'align' => 'left'),
                array('data' => apbd_fn($data->totalp), 'align' => 'right'),
                array('data' => $data->sumberdana1, 'align' => 'left'),
                array('data' => $editlink, 'align' => 'right'),
            );
        }
    } else {
        $rows[] = array (
            array('data' => 'Data kosong, tidak bisa menambahkan perubahan', 'colspan'=>'3')
        );
    }
	
	
    $output .= theme ('pager', NULL, $limit, 0);
    return $output;
}

function kegiatanrevisi2_filter_form() {

}

function kegiatanrevisi2_filter_form_submit($form, &$form_state) {
}
?>
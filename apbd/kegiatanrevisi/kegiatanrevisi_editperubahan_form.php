<?php
function kegiatanrevisi_editperubahan_form() {	
	drupal_add_css('files/css/kegiatancam.css');
	
	drupal_set_title('Usulan Perubahan');
	
	$id = arg(3);
	if ($id=='') $id = '0';
	$kodeuk = arg(4);
	$kodekeg = arg(5);
	
	//drupal_set_message('id : ' . $id);
	//drupal_set_message('kode : ' . $kodekeg);
	

	if (isset($id) and ($id != '0')) {
        $sql = 'select id,alasan1, alasan2, alasan3, nosurat, tglsurat,dokumen,kodekeg,kodeuk from {kegiatanrevisiperubahan} where id=\'%s\'';
		$res = db_query(db_rewrite_sql($sql), array ($id));
		if ($res) {
			if ($data = db_fetch_object($res)) {
				$id = $data->id;
				$alasan1 = $data->alasan1;
				$alasan2 = $data->alasan2;
				$alasan3 = $data->alasan3;
				$nosurat = $data->nosurat;
				$tglsurat = $data->tglsurat;
				$dokumen = $data->dokumen;

				$kodekeg = $data->kodekeg;
				$kodeuk = $data->kodeuk;
			}
		} else
			$id = '0';
	} else 
		$id = '0';
	
	//Kegiatan
	$kegiatan = 'Kegiatan';
	$sql = 'select kegiatan from {kegiatanperubahan} where kodekeg=\'%s\'';
	$res = db_query(db_rewrite_sql($sql), array ($kodekeg));
	if ($res) {
		if ($data = db_fetch_object($res)) {
			$kegiatan = $data->kegiatan;

		}
	} 
	
	drupal_set_title('Usulan Perubahan ' . $kegiatan);
	
	$form['id']= array(
		'#type' => 'value', 
		'#value' => $id, // changed
	);
	$form['kodeuk']= array(
		'#type' => 'value', 
		'#value' => $kodeuk, // changed
	);

	$form['kodekeg']= array(
		'#type' => 'value', 
		'#value' => $kodekeg, // changed
	);
	
	$form['alasan1'] = array(
		'#type' => 'textfield',
		'#title' => 'Alasan Perubahan #1',
		'#maxlength'    => 255, 
		'#size'         => 120, 
		'#default_value' => $alasan1,
		'#required' => true,
	);

	$form['alasan2'] = array(
		'#type' => 'textfield',
		'#title' => 'Alasan Perubahan #2',
		'#maxlength'    => 255, 
		'#size'         => 120, 
		'#default_value' => $alasan2,
	);

	$form['alasan3'] = array(
		'#type' => 'textfield',
		'#title' => 'Alasan Perubahan #3',
		'#maxlength'    => 255, 
		'#size'         => 120, 
		'#default_value' => $alasan3,
	);

	$form['dokumen'] = array(
		'#type' => 'textfield',
		'#title' => 'Dokumen Pendukung',
		'#maxlength'    => 255, 
		'#size'         => 120, 
		'#default_value' => $dokumen,
	);

	$form['nosurat'] = array(
		'#type' => 'textfield',
		'#title' => 'No. Surat',
		'#maxlength'    => 255, 
		'#size'         => 60, 
		'#default_value' => $nosurat,
		'#required' => true,
	);

	$form['tglsurat'] = array(
		'#type' => 'textfield',
		'#title' => 'Tgl. Surat',
		'#maxlength'    => 255, 
		'#size'         => 60, 
		'#default_value' => $tglsurat,
		'#required' => true,
	);
	
	$form['lanjut'] = array(
		'#type' => 'submit',
		'#suffix' => "&nbsp;<a href='/apbd/kegiatanrevisiperubahan' class='btn_green' style='color: white'>Batal</a>",
		'#value' => 'Simpan',
	);
	return $form;
	
}

function kegiatanrevisi_editperubahan_form_validate($form, &$form_state) {
}
 
function kegiatanrevisi_editperubahan_form_submit($form, &$form_state) {
	
	$id = $form_state['values']['id'];
	$kodeuk = $form_state['values']['kodeuk'];
	$kodekeg = $form_state['values']['kodekeg'];
	
	$subjenisrevisi = 1;
	$geserblokir = 1;
	$geserrincian = 1;
	$geserobyek = 1;
	$lokasi = 1;
	$sumberdana = 1;
	$kinerja = 1;
	$sasaran = 1;
	$detiluraian = 1;
	$rab = 1;
	$triwulan = 1;
	$lainnya = 0;
			
	$alasan1 = $form_state['values']['alasan1'];
	$alasan2 = $form_state['values']['alasan2'];
	$alasan3 = $form_state['values']['alasan3'];
	 
	$nosurat = $form_state['values']['nosurat'];
	$tglsurat = $form_state['values']['tglsurat'];	
	
	$dokumen = $form_state['values']['dokumen'];
	
	$tahun = variable_get('apbdtahun', 0);
  
		if ($id=='0') {
			$sql =  sprintf("insert into {kegiatanrevisiperubahan} (jenisrevisi, subjenisrevisi, tahun, kodeuk, kodekeg, geserblokir, geserrincian, geserobyek, lokasi, sumberdana, kinerja, sasaran, detiluraian, rab, triwulan, lainnya, alasan1, alasan2, alasan3, nosurat, tglsurat, dokumen) values('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')", $jenisrevisi, $subjenisrevisi, $tahun, $kodeuk, $kodekeg, $geserblokir, $geserrincian, $geserobyek, $lokasi, db_escape_string($sumberdana), db_escape_string($kinerja), db_escape_string($sasaran), db_escape_string($detiluraian), db_escape_string($rab), db_escape_string($triwulan), db_escape_string($lainnya), db_escape_string($alasan1), db_escape_string($alasan2), db_escape_string($alasan3), db_escape_string($nosurat), db_escape_string($tglsurat), db_escape_string($dokumen)); 
			
			$ada = false;
			
		} else {
			$sql = sprintf("update {kegiatanrevisiperubahan} set alasan1='%s', alasan2='%s', alasan3='%s', nosurat='%s', tglsurat='%s', dokumen='%s' where id='%s'",  db_escape_string($alasan1), db_escape_string($alasan2), db_escape_string($alasan3), db_escape_string($nosurat), db_escape_string($tglsurat), db_escape_string($dokumen), db_escape_string($id));
			
			$ada = true;
		}
		//drupal_set_message($sql);
		$res = db_query($sql);
		
		//if (($kodekeg!='') and ($kodekeg != $kodekeg)) {
		if ($ada==false) {	
			//DELETE EXISTING
			/*
            $query = sprintf("delete from {kegiatanrevisi} where kodekeg='%s'", db_escape_string($kodekeg));
            $res = db_query($query);
			if ($res == false) $emsg .= '1';
			
            $query = sprintf("delete from {anggperkegrevisi} where kodekeg='%s'", db_escape_string($kodekeg));
            $res = db_query($query);
			if ($res == false) $emsg .= '2';

            $res = $query = sprintf("delete from {anggperkegdetilsubrevisi} where iddetil in (select iddetil from anggperkegdetilrevisi kodekeg='%s')", db_escape_string($kodekeg));
            $res = db_query($query);
			if ($res == false) $emsg .= '3';

            $res = $query = sprintf("delete from {anggperkegdetilrevisi} where kodekeg='%s'", db_escape_string($kodekeg));
            $res = db_query($query);
			if ($res == false) $emsg .= '4';
			*/

			//REINSERT
            $query = sprintf("insert into {kegiatanrevisi} (kodekeg, nomorkeg, jenis, tahun, kodepro, kodeuk, kegiatan, lokasi, totalsebelum, totalsesudah, total, plafon, targetsesudah, kodesuk, sumberdana1, sumberdana2, sumberdana1rp, sumberdana2rp, periode, programsasaran, programtarget, masukansasaran, masukantarget, keluaransasaran, keluarantarget, hasilsasaran, hasiltarget, waktupelaksanaan, latarbelakang, kelompoksasaran, tw1, tw2, tw3, tw4, adminok, inaktif, isgaji, isppkd, plafonlama, dispensasi, edit, plafonpenetapan, totalpenetapan, anggaran, bintang) select kodekeg, nomorkeg, jenis, tahun, kodepro, kodeuk, kegiatan, lokasi, totalsebelum, totalsesudah, totalp total, plafon, targetsesudah, kodesuk, sumberdana1, sumberdana2, sumberdana1rp, sumberdana2rp, periode, programsasaran, programtarget, masukansasaran, masukantarget, keluaransasaran, keluarantarget, hasilsasaran, hasiltarget, waktupelaksanaan, latarbelakang, kelompoksasaran, tw1p, tw2p, tw3p, tw4p, adminok, inaktif, isgaji, isppkd, plafonlama, dispensasi, edit, plafonlama plafonpenetapan, total totalpenetapan, anggaran, bintang from {kegiatanperubahan} where kodekeg='%s'", db_escape_string($kodekeg));
            //$query = sprintf("insert into {kegiatanrevisi} (kodekeg, nomorkeg, jenis, tahun, kodepro, kodeuk, kegiatan, lokasi, totalsebelum, totalsesudah, total, plafon, targetsesudah, kodesuk, sumberdana1, sumberdana2, sumberdana1rp, sumberdana2rp, periode, programsasaran, programtarget, masukansasaran, masukantarget, keluaransasaran, keluarantarget, hasilsasaran, hasiltarget, waktupelaksanaan, latarbelakang, kelompoksasaran, tw1, tw2, tw3, tw4, adminok, inaktif, isgaji, isppkd, plafonlama, dispensasi, edit, plafonpenetapan, totalpenetapan) select kodekeg, nomorkeg, jenis, tahun, kodepro, kodeuk, kegiatan, lokasi, totalsebelum, totalsesudah, total, plafon, targetsesudah, kodesuk, sumberdana1, sumberdana2, sumberdana1rp, sumberdana2rp, periode, programsasaran, programtarget, masukansasaran, masukantarget, keluaransasaran, keluarantarget, hasilsasaran, hasiltarget, waktupelaksanaan, latarbelakang, kelompoksasaran, tw1, tw2, tw3, tw4, adminok, inaktif, isgaji, isppkd, plafonlama, dispensasi, edit, plafon plafonpenetapan, total totalpenetapan from {kegiatanskpd} where kodekeg='%s'", db_escape_string($kodekeg));
			 
            $res = db_query($query);
			if ($res == false) $emsg .= '5';
			
            $query = sprintf("insert into {anggperkegrevisi} (kodero, kodekeg, uraian, jumlah, jumlahsesudah, jumlahsebelum, anggaran, bintang) select kodero, kodekeg, uraian, jumlahp jumlah, jumlahsesudah, jumlahsebelum, anggaran, bintang from {anggperkegperubahan} where kodekeg='%s'", db_escape_string($kodekeg));
            //$query = sprintf("insert into {anggperkegrevisi} (kodero, kodekeg, uraian, jumlah, jumlahsesudah, jumlahsebelum) select kodero, kodekeg, uraian, jumlah, jumlahsesudah, jumlahsebelum from {anggperkeg} where kodekeg='%s'", db_escape_string($kodekeg));
			//drupal_set_message($query);
            $res = db_query($query);
			if ($res == false) $emsg .= '6';

            $query = sprintf("insert into {anggperkegdetilrevisi} (iddetil, kodero, kodekeg, pengelompokan, uraian, unitjumlah, unitsatuan, volumjumlah, volumsatuan, harga, total, nourut, anggaran, bintang) select distinct iddetil, kodero, kodekeg, pengelompokan, uraian, unitjumlah, unitsatuan, volumjumlah, volumsatuan, harga, total, nourut, anggaran, bintang from {anggperkegdetilperubahan} where kodekeg='%s'", db_escape_string($kodekeg));
            //$query = sprintf("insert into {anggperkegdetilrevisi} (iddetil, kodero, kodekeg, pengelompokan, uraian, unitjumlah, unitsatuan, volumjumlah, volumsatuan, harga, total, nourut) select iddetil, kodero, kodekeg, pengelompokan, uraian, unitjumlah, unitsatuan, volumjumlah, volumsatuan, harga, total, nourut from {anggperkegdetil} where kodekeg='%s'", db_escape_string($kodekeg));
			//drupal_set_message($query);
            $res = db_query($query);
			if ($res == false) $emsg .= '7';		
			
            $query = sprintf("insert into {anggperkegdetilsubrevisi} (idsub, iddetil, uraian, unitjumlah, unitsatuan, volumjumlah, volumsatuan, harga, total, nourut, anggaran, bintang) select distinct idsub, iddetil, uraian, unitjumlah, unitsatuan, volumjumlah, volumsatuan, harga, total, nourut, anggaran, bintang from {anggperkegdetilsubperubahan} where iddetil in (select iddetil from anggperkegdetilperubahan where kodekeg='%s')", db_escape_string($kodekeg));
			
			//$query = sprintf("insert into {anggperkegdetilsubrevisi} (idsub, iddetil, uraian, unitjumlah, unitsatuan, volumjumlah, volumsatuan, harga, total, nourut) select idsub, iddetil, uraian, unitjumlah, unitsatuan, volumjumlah, volumsatuan, harga, total, nourut from {anggperkegdetilsub} where iddetil in (select iddetil from anggperkegdetilsub where kodekeg='%s')", db_escape_string($kodekeg));
			
			//drupal_set_message($query);
			
            $res = db_query($query);
			if ($res == false) $emsg .= '8';		

		}
		
		
		//UPDATE ALASAN PERUBAHAN
		$sql = sprintf("update {kegiatanrevisi} set latarbelakang='%s' where kodekeg='%s'", db_escape_string($alasan1), db_escape_string($kodekeg));
		//drupal_set_message($sql);
		$res = db_query($sql);
		if ($res == false) $emsg .= '7';		
  
		if ($res) 
			drupal_set_message('Penyimpanan data berhasil dilakukan');
		else
			drupal_set_message('Penyimpanan data tidak berhasil dilakukan');	

		drupal_goto('apbd/kegiatanrevisiperubahan');
		
		
}

?>
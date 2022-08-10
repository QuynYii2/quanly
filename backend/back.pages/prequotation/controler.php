<?php
class controler {
	var $_db;
    var $_genre;
    var $_module;
    var $_permission;

	function __construct() {
        $this->_genre = Generals::getVar('option', 'prequotation');
        $this->_module = Generals::getVar('option', 'index');
        $this->_permission = Generals::getSession('permission');
        $this->controler();
	}

	function controler(){
		$this->_db = new QuotationDao($this->_genre);
	}

	function display() {
        $view = Generals::getVar('view', 'list');
        if (empty($this->_permission['mod_simple'][$this->_module]) && $view == 'list'):
            Generals::redirect('index.php?option=permission');
        elseif (empty($this->_permission['mod_update'][$this->_module]) && $view == 'form'):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        if ($view == 'list') Generals::setState('data.form', null);
	}

	function ordering() {
        if (empty($this->_permission['mod_update'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        $orders = Generals::getVar('order', array());
		if (is_array($orders)) foreach ($orders as $key => $val) $this->_db->ordering($val, $key);
		Generals::setError(Generals::getTitle('ORDERING_SUCCESS'));
		Generals::redirect('index.php?option=prequotation&view=list');
	}

	function delete() {
        if (empty($this->_permission['mod_delete'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        $cid = Generals::getVar('cid', array());
		if (is_array($cid)) foreach ($cid as $val) $this->_db->delete($val);
		Generals::setError(str_replace('%s', count($cid), Generals::getTitle('DELETES_SUCCESS')));
		Generals::redirect('index.php?option=prequotation&view=list');
	}

	function create() {
        if (empty($this->_permission['mod_create'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        header('location: index.php?option=prequotation&view=form');
		return false;
	}

	function publish() {
        if (empty($this->_permission['mod_update'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        $cid = Generals::getVar('cid', array());
		$this->_db->published(1, $cid);
		Generals::setError(str_replace('%s', count($cid), Generals::getTitle('PUBLISH_SUCCESS')));
		Generals::redirect('index.php?option=prequotation&view=list');
	}

	function unpublish() {
        if (empty($this->_permission['mod_update'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        $cid = Generals::getVar('cid', array());
		$this->_db->published(0, $cid);
		Generals::setError(str_replace('%s', count($cid), Generals::getTitle('UNPUBLISH_SUCCESS')));
		Generals::redirect('index.php?option=prequotation&view=list');
	}

    function abolition() {
        if (empty($this->_permission['mod_update'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        $cid = Generals::getVar('cid', array());
        $this->_db->published(2, $cid);
        Generals::setError(str_replace('%s', count($cid), Generals::getTitle('ABOLITION_SUCCESS')));
        Generals::redirect('index.php?option=extquotation&view=list');
    }

	function trash() {
        if (empty($this->_permission['mod_delete'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        $cid = Generals::getVar('cid', array());
		$this->_db->published(-1, $cid[0]);
	}
	
	function apply() { $this->save('apply'); }
	
	function update() { $this->save('save'); }
	
	function save($task, $function = null) {
        $only   = Generals::getVar('only', 0);
		$vform 	= Generals::getVar('vform', array());
		$cid	= Generals::getVar('cid', array(0));
		$user	= Generals::getUserData();
		
		$vform['id'] = (int)$cid[0];

        if (empty($only)):
            $vform['genre'] = $this->_genre;
            $vform['agency'] = $vform['agencies'][0];
            $vform['agencies'] = !empty($vform['agencies']) ? ','.implode(',', $vform['agencies']).',' : null;
            $vform["departure"] = $vform["departure"] ? date('Y-m-d H:i:s', strtotime($vform["departure"])) : null;

            if (!$vform['id']):
                $vform["create_on"] = date('Y-m-d H:i:s');
                $vform["create_by"] = $user['id'];
            else:
                $vform["update_on"] = date('Y-m-d H:i:s');
                $vform["update_by"] = $user['id'];
            endif;

            if (empty($this->_permission['mod_create'][$this->_module]) && empty($vform['id'])):
                Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
                Generals::redirect('index.php?option='.$this->_module.'&view=list');
            elseif (empty($this->_permission['mod_update'][$this->_module]) && !empty($vform['id'])):
                Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
                Generals::redirect('index.php?option='.$this->_module.'&view=list');
            endif;

            #=========================================================================================
            # Check Existed Code =====================================================================
            #=========================================================================================
            if ($this->_db->getExistedCode($vform['code'], (int)$vform['id'])):
                Generals::setState('data.form', $vform);
                Generals::setWarning(Generals::getTitle('CODE_EXISTED'));
                Generals::redirect('index.php?option='.$this->_module.'&view=form&cid[]='. (int)$vform['id']);
            endif;
            #=========================================================================================
            # File thumbnail social ==================================================================
            #=========================================================================================
            if ($vform["remsocial"]):
                $DiskPath = IMG_PATH.DS."prequotation";
                if (JFile::exists($DiskPath.DS.Generals::getVar('hiddensocial'))) JFile::delete($DiskPath.DS.Generals::getVar('hiddensocial'));
                $vform['social'] = '';
            endif;

            $file_social = $_FILES['social'];

            if($file_social['name'] != '')  {
                if (!Generals::addFile('social', 'normal', IMG_PATH.DS."prequotation")) {
                    $link= 'index.php?option=prequotation&view=form&cid[]='. $vform['id'];
                    Generals::redirect($link); return;
                } else {
                    $vform['social'] = Generals::getVar('social');
                }
            }
            #=========================================================================================
            #=========================================================================================
            $data = $this->_db->getDataMapTable($vform, 'tbl_quotation');

            $this->_db->beginTrans();
            try {
                if ($data['id']):
                    $this->_db->update_db('tbl_quotation', $data, ' id = ? ', array($data['id']));
                else:
                    $this->_db->insert_db('tbl_quotation', $data);
                endif;

                $this->_db->commitTrans();
                Generals::setError(Generals::getTitle('UPDATE_SUCCESS'));
                $vform['id'] = $data['id'] ? $data['id'] : $this->_db->getLastInsertId();
                if ($task == "save"):
                    $link = 'index.php?option=prequotation&view=list';
                elseif ($task == "apply"):
                    $link = 'index.php?option=prequotation&view=form&cid[]='. $vform['id'] .'';
                endif;
            } catch (Exception $ex) {
                $this->_db->rollbackTrans();
                Generals::setError(Generals::getTitle('UPDATE_EROR'));
                $link = 'index.php?option=prequotation&view=form&cid[]='. $vform['id'] .'';
                Generals::redirect($link);
            }
            #----------------------------------------------------------------
            #----------------------------------------------------------------
            $_name 	= array_values($vform["name"]);
            $_intro	= array_values($vform["introtext"]);
            $_tags	= is_array($vform["tags"]) ? array_values($vform["tags"]) : array();
            $_meta	= is_array($vform["meta"]) ? array_values($vform["meta"]) : array();

            $this->_db->beginTrans();
            try {
                if (is_array($vform["name"])) foreach ($vform["name"] as $lang => $name):
                    $data = array();
                    $data['id']			= (int)$vform['oldid'][$lang];
                    $data['quotation'] 	= $vform['id'];
                    $data['language']	= $lang;
                    $data['name']		= $name ? $name : $_name[0];
                    $data['alias']		= JFilterOutput::stringURLSafe(Generals::getCovertVn($data['name']));
                    $data['introtext']	= $vform["introtext"][$lang] ? $vform["introtext"][$lang] : $_intro[0];
                    $data['tags']		= $vform["tags"][$lang] ? $vform["tags"][$lang] : $_tags[0];
                    $data['meta']		= $vform["meta"][$lang] ? $vform["meta"][$lang] : $_meta[0];
                    if ($data['id']):
                        $this->_db->update_db('tbl_quotation_lang', $data, ' id = ? ', array($data['id']));
                    else:
                        $this->_db->insert_db('tbl_quotation_lang', $data);
                    endif;
                endforeach;
                $this->_db->commitTrans();
            } catch (Exception $ex) {
                $this->_db->rollbackTrans();
                Generals::setError(Generals::getTitle('UPDATE_EROR'));
                $link = 'index.php?option=prequotation&view=form&cid[]='. $vform['id'] .'';
                Generals::redirect($link);
            }
            #----------------------------------------------------------------
            # Update Quotation List -----------------------------------------
            #----------------------------------------------------------------
            $vdata = Generals::getVar('vdata', array());

            $jid = $vdata['jid'];
            $jid[] = 0;
            $sid[] = 0;
            if (is_array($vdata['sid'])) foreach ($vdata['sid'] as $ids):
                if (is_array($ids)) foreach ($ids as $id):
                    $sid[] = $id;
                endforeach;
            endforeach;

            $this->_db->beginTrans();
            $this->_db->delete_db('tbl_journey', ' quotation = ? AND id NOT IN('.implode(',', $jid).') ', array($vform['id']));
            $this->_db->delete_db('tbl_journey_service', ' quotation = ? AND id NOT IN('.implode(',', $sid).') ', array($vform['id']));
            $this->_db->commitTrans();

            $this->_db->beginTrans();
            try {
                $_fnames  = $this->_db->getLocations($vdata['f_location'][0]);
                $journeis = array($_fnames[0]['name']);

                if (is_array($vdata['f_location'])) foreach ($vdata['f_location'] as $key => $temp1):
                    $_fnames = $this->_db->getLocations($vdata['f_location'][$key]);
                    $_tnames = $this->_db->getLocations($vdata['t_location'][$key]);
                    $journeis[] = $_tnames[0]['name'];

                    $journey_id = $vdata['jid'][$key];
                    $service_id = $vdata['sid'][$key];
                    $f_location = $vdata['f_location'][$key];
                    $t_location = $vdata['t_location'][$key];
                    $introtext  = $vdata['introtext'][$key];
                    $service    = $vdata['service'][$key];
                    $location   = $vdata['location'][$key];
                    $profile    = $vdata['profile'][$key];
                    $detail     = $vdata['detail'][$key];
                    $markup     = $vdata['markup'][$key];
                    $discount   = $vdata['discount'][$key];
                    $conversion = $vdata['conversion'][$key];
                    $price      = $vdata['price'][$key];
                    $update     = $vdata['update'][$key];

                    foreach ($update as $val):
                        if ($val):
                            $isedit = true;
                            break;
                        endif;
                    endforeach;

                    if (!empty($f_location) && !empty($t_location)):
                        $data = array();
                        $data['numday']     = (int)$key+1;
                        $data['quotation']  = $vform['id'];
                        $data['f_location'] = $f_location;
                        $data['t_location'] = $t_location;
                        $data['introtext']  = $introtext;
                        $data['f_name']     = $_fnames[0]['name'];
                        $data['t_name']     = $_tnames[0]['name'];

                        if ($isedit):
                            if (!$journey_id):
                                $this->_db->insert_db('tbl_journey', $data);
                                $journey = $this->_db->getLastInsertId();
                            else:
                                $this->_db->update_db('tbl_journey', $data, ' id = ? ', array($journey_id));
                                $journey = $journey_id;
                            endif;
                        endif;
                        #========================================================
                        if (is_array($service)):
                            foreach ($detail as $i => $temp2):
                                if (!empty($temp2) && $update[$i]):
                                    if (empty($service[$i])):
                                        $servergenre = $this->_db->getServiceData($profile[$i]);
                                        $service[$i] = $servergenre['genre'];
                                    endif;
                                    $convert= number_format($conversion[$i] ? $conversion[$i] : ($vform['conversion'] ? $vform['conversion'] : 1), 2);
                                    $prices = $this->getPrice($service[$i], $detail[$i], $convert, $price[$i]);
                                    $fnames = $this->_db->getLocations($f_location);
                                    $tnames = $this->_db->getLocations($t_location);

                                    $data = array();
                                    $data['quotation']  = $vform['id'];
                                    $data['journey']    = $journey;
                                    $data['service']    = $service[$i];
                                    $data['location']   = (int)$location[$i];
                                    $data['profile']    = (int)$profile[$i];
                                    $data['detail']     = (int)$detail[$i];
                                    $data['markup']     = number_format($markup[$i] ? $markup[$i] : $vform['markup'], 0);
                                    $data['discount']   = number_format($discount[$i] ? $discount[$i] : $vform['discount'], 0);
                                    $data['conversion'] = number_format($conversion[$i] ? $conversion[$i] : ($vform['conversion'] ? $vform['conversion'] : 1), 2, '.', '');
                                    $data['price']      = number_format($prices['cost'], 2, '.', '');
                                    $data['single']     = number_format($prices['single'], 2, '.', '');
                                    $data['perpax']     = json_encode($prices['perpax']);
                                    $data['fromname']   = $fnames[0]['name'];
                                    $data['toname']     = $tnames[0]['name'];
                                    $data['detailname'] = $prices['name'];
                                    $data['detailcode'] = $prices['code'];
                                    $paxrange = $data['perpax'];

                                    if (!$service_id[$i]):
                                        $this->_db->insert_db('tbl_journey_service', $data);
                                    else:
                                        $this->_db->update_db('tbl_journey_service', $data, ' id = ? ', array($service_id[$i]));
                                    endif;
                                endif;
                            endforeach;
                        endif;
                        if ($isedit) $this->_db->update_db('tbl_journey', array('paxrange' => $paxrange), ' id = ? ', array($journey));
                    endif;
                endforeach;
                if ($isedit) $this->_db->update_db('tbl_quotation', array('journeis' => implode(' - ', $journeis), 'paxrange' => $paxrange), ' id = ? ', array($vform['id']));
                $this->_db->commitTrans();
            } catch (Exception $ex) {
                $this->_db->rollbackTrans();
                Generals::setError(Generals::getTitle('UPDATE_EROR'));
                $link = 'index.php?option=prequotation&view=form&cid[]='. $vform['id'] .'';
                Generals::redirect($link);
            }
        endif;
        #----------------------------------------------------------------
        if ($function == 'export') $this->export2excel($vform['id']);
        if ($function == 'sendto'):
            $agencies = $this->_db->getAgencies('0'.$vform['agencies'].'0');
            $filepath = $this->export2excel($vform['id'], true);
            $config   = Generals::getConfig();

            if (is_array($agencies)) foreach ($agencies as $agency):
                $mail = new PHPMailer;
                $mail->isSMTP();
                $mail->Host = $config['config:host'];
                $mail->Username = $config['config:user'];
                $mail->Password = $config['config:pass'];
                $mail->Port = $config['config:port'];
                $mail->SMTPAuth = true;
                $mail->SMTPSecure = 'tls';

                $mail->From = $agency["email"];
                $mail->FromName = $agency["name"];
                $mail->CharSet = 'UTF-8';
                $mail->addAddress($config['config:email']);
                $mail->addReplyTo($config['config:email']);

                $mail->WordWrap = 50;
                $mail->isHTML(true);

                $mail->Subject = implode(' - ', $journeis);
                $mail->Body    = implode(' - ', $journeis);
                $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
                $mail->addAttachment($filepath);
                if ($_SERVER["SERVER_NAME"] != "localhost") $mail->send();
            endforeach;
        endif;
        #----------------------------------------------------------------
        Generals::setState('data.form', null);
		Generals::redirect($link);
	}
	
	function cancel() {
		Generals::redirect('index.php?option=prequotation&view=list');
	}

    #=================================================================================
    # Ajax Function ==================================================================
    #=================================================================================
    function getParentServices(){
        $genre = Generals::getVar('genre');
        $location = Generals::getVar('location');
        $setvalue = Generals::getVar('setvalue');
        $result = $this->_db->getServices($genre, $location);
        $return = '<option value="">'.Generals::getTitle('PROFILE').'[i]'.Generals::getTitle('LOCATION').'[/i]</option>';
        $suname = '';
        if (is_array($result)) foreach ($result as $i => $items):
            if ($suname != $items['su_name']):
                $return.= '<optgroup label="'.$items['su_name'].'">';
                $suname = $items['su_name'];
            endif;
            $return.= '<option value="'.$items['id'].'" '.($setvalue==$items['id']?'selected="selected"':'').'>'.$items['code'].' - '.$items['name'].'[i]'.$items['lo_name'].'[/i]</option>';
            if ($suname != $items['su_name'] || ($i+1) == count($result)):
                $return.= '</optgroup>';
            endif;
        endforeach;

        echo $return;
    }

    function getChildrenService(){
        $genre  = Generals::getVar('genre');
        $parent = Generals::getVar('parent');
        $arrival = Generals::getVar('arrival');
        $setvalue = Generals::getVar('setvalue');

        if (empty($genre)):
            $service = $this->_db->getServiceData($parent);
            $genre   = $service['genre'];
        endif;

        $result = $this->_db->getSubServices($genre, $parent, $arrival);
        $return = '<option value="">'.Generals::getTitle('DETAIL').'</option>';

        if (is_array($result)) foreach ($result as $items):
            $return.= '<option value="'.$items['id'].'" '.($setvalue==$items['id']?'selected="selected"':'').'>'.$items['code'].' - '.$items['name'].'</option>';
        endforeach;

        echo $return;
    }

    function getDetailService() {
        $day    = Generals::getVar('day');
        $num    = Generals::getVar('num');
        $price  = Generals::getVar('price');
        $genre  = Generals::getVar('genre');
        $subid  = Generals::getVar('id');
        $parent = Generals::getVar('parent');
        $convert= Generals::getVar('convert', 1);
        $price  = $price ? str_replace(',', '', $price) : 0;
        if (empty($genre)):
            $service = $this->_db->getServiceData($parent);
            $genre   = $service['genre'];
        endif;

        $person = $this->_db->getPersons();
        $result = $this->_db->getDetailService($genre, $subid);

        if ($price > 0 && is_numeric($price)):
            $result['cost'] = $price * $convert;
        endif;

        $return = '<tr class="bg-info">\n';
        $return.= ' <td class="text-center prices"><input type="text" id="control-'.$day.'-'.$num.'-7" name="vdata[price]['.$day.']['.$num.']" field="price" class="form-control number price" value="'.number_format($result['cost']/$convert, 2).'"><input type="hidden" name="vdata[update]['.$day.']['.$num.']" class="update" value="1"/></td>';
        if (is_array($person)) foreach ($person as $items):
            $items['divpax'] = $items['divpax'] ? $items['divpax'] : ($result['perpax']?$items['maxpax']:$items['minpax']);
            if ($result['perpax']):
                $count = ceil($items['divpax']/$result['perpax']);
                $price = number_format((($result['cost']*$count)/$items['divpax'])/$convert, 2);
            else:
                $price = number_format(($result['cost']/$items['divpax'])/$convert, 2);
            endif;
            if (strpos($result['paxranges'], ','.$items['id'].',') === false) $price = null;
            $return.= ' <td class="text-center perpax" minpax="'.$items['minpax'].'" maxpax="'.$items['maxpax'].'" divpax="'.$items['divpax'].'">'.$price.'</td>';
        endforeach;
        $return.= ' <td class="text-center single">'.($result['single']?number_format($result['single']/$convert, 2):'').'</td>';
        $return.= '</tr>\n';

        echo $return;
    }

    function getPrice($genre, $subid, $convert = 1, $overwrite = 0) {
        $convert= $convert ? $convert : 1;
        $person = $this->_db->getPersons();
        $result = $this->_db->getDetailService($genre, $subid);

        $return = array();
        $return['code']     = $result['code'];
        $return['name']     = $result['name'];
        $return['cost']     = $overwrite ? $overwrite : $result['cost']/$convert;
        $return['single']   = $result['single']/$convert;
        $return['perpax']   = array();
        if (is_array($person)) foreach ($person as $items):
            $items['divpax'] = $items['divpax'] ? $items['divpax'] : ($result['perpax']?$items['maxpax']:$items['minpax']);
            if (strpos($result['paxranges'], ','.$items['id'].',') === false):
                $return['perpax'][] = array('label' => $items['name'], 'value' => null, 'range' => $items['divpax']);
            else:
                if ($result['perpax']): # Neu gia ap dung theo dau nguoi thi lay max de duoc so phong lon nhat
                    $count = ceil($items['divpax']/$result['perpax']);
                    $return['perpax'][] = array('label' => $items['name'], 'value' => number_format((($result['cost']*$count)/$items['divpax'])/$convert, 2, '.', ''), 'range' => $items['divpax']);
                else: # Neu gia khong ap dung theo dau nguoi thi lay min de ra so tien lon nhat
                    $return['perpax'][] = array('label' => $items['name'], 'value' => number_format(($result['cost']/$items['divpax'])/$convert, 2, '.', ''), 'range' => $items['divpax']);
                endif;
            endif;
        endforeach;

        return $return;
    }

    function export(){ $this->save('apply', 'export'); }

    function sendto(){ $this->save('apply', 'sendto'); }

    function export2excel($qid, $save = false){
        $quotation   = $this->_db->getData($qid);
        $journeies   = $this->_db->getJourneies($qid);
        $paxranges   = json_decode($quotation['paxrange']);
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
            ->setLastModifiedBy("Maarten Balliauw")
            ->setTitle("PHPExcel Test Document")
            ->setSubject("PHPExcel Test Document")
            ->setDescription("Test document for PHPExcel, generated using PHP classes.")
            ->setKeywords("office PHPExcel php")
            ->setCategory("Test result file");

        # Line First Empty
        $sheet = $objPHPExcel->setActiveSheetIndex(0);
        $sheet->getDefaultStyle()->applyFromArray(array('font'=>array('size'=>10)));
        $sheet->setCellValue('G2', Generals::getTitle('EXPORT_SCALE'))
            ->getStyle('G2')->applyFromArray(
                array(
                    'alignment'=>array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                    'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => '7030A0')),
                    'font' => array('color' => array('rgb' => 'FFFFFF'))
                )
            );
        ;
        if (is_array($paxranges)) foreach ($paxranges as $key => $items):
            $column = $this->getColumnExcel($key+7);
            $sheet->setCellValue($column.'2', $items->label)
                ->getStyle($column.'2')->applyFromArray(
                    array(
                        'alignment'=>array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                        'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => '7030A0')),
                        'font' => array('color' => array('rgb' => 'FFFFFF'))
                    )
                );
        endforeach;
        $column = $this->getColumnExcel($key+8);
        $sheet->setCellValue($column.'2', Generals::getTitle('EXPORT_SINGLE'))
            ->getStyle($column.'2')->applyFromArray(
                array(
                    'alignment'=>array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                    'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => '7030A0')),
                    'font' => array('color' => array('rgb' => 'FFFFFF'))
                )
            );

        $sheet->setCellValue('A3', Generals::getTitle('EXPORT_DAYS'));
        $sheet->setCellValue('B3', Generals::getTitle('EXPORT_FROM'));
        $sheet->setCellValue('C3', Generals::getTitle('EXPORT_TO'));
        $sheet->setCellValue('D3', Generals::getTitle('EXPORT_CODE'));
        $sheet->setCellValue('E3', Generals::getTitle('EXPORT_INTRO'));
        $sheet->setCellValue('F3', Generals::getTitle('EXPORT_PRICE'));
        $sheet->setCellValue('G3', Generals::getTitle('EXPORT_RANGE'))
            ->getStyle('G3')->applyFromArray(
                array(
                    'alignment'=>array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                    'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'C9C9C9')),
                    'font' => array('color' => array('rgb' => '0070C0'))
                )
            );

        if (is_array($paxranges)) foreach ($paxranges as $key => $items):
            $column = $this->getColumnExcel($key+7);
            $sheet->setCellValue($column.'3', $items->range)
                ->getStyle($column.'3')->applyFromArray(
                    array(
                        'alignment'=>array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                        'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'C9C9C9')),
                        'font' => array('color' => array('rgb' => 'FF0000'))
                    )
                );
        endforeach;
        $column = $this->getColumnExcel($key+8);
        $sheet->setCellValue($column.'3', '')
            ->getStyle($column.'3')->applyFromArray(
                array(
                    'alignment'=>array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                    'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'C9C9C9')),
                    'font' => array('color' => array('rgb' => 'FF0000'))
                )
            );
        $sheet->freezePaneByColumnAndRow(0, 4);
        #==============================================================================================
        #==============================================================================================
        $next = 5;
        $markup = array();
        $discount = array();
        if (is_array($journeies)) foreach ($journeies as $day => $journey):
            $sheet->setCellValue('A'.$next, Generals::getTitle('EXPORT_DAY').' '.($day+1));
            $sheet->setCellValue('B'.$next, $journey['f_name']);
            $sheet->setCellValue('C'.$next, $journey['t_name']);
            $sheet->setCellValue('E'.$next, $journey['introtext']);
            $next++;
            if (is_array($journey['services'])) foreach ($journey['services'] as $service):
                $sheet->setCellValue('D'.$next, $service['detailcode']);
                $sheet->setCellValue('E'.$next, $service['detailname']);
                $sheet->setCellValue('F'.$next, $service['price']);
                $ranges = json_decode($service['perpax']);
                if (is_array($ranges)) foreach ($ranges as $key => $pax):
                    $column = $this->getColumnExcel($key+7);
                    $sheet->setCellValue($column.$next, $pax->value);
                    $markup[$column]+= ($pax->value*$service['markup'])/100;
                    $discount[$column]+= $pax->value+((100-$service['discount'])/100);
                endforeach;
                $column = $this->getColumnExcel($key+8);
                $sheet->setCellValue($column.$next, $service['single']);
                $markup[$column]+= ($service['single']*$service['markup'])/100;
                $discount[$column]+= $service['single']+((100-$service['discount'])/100);

                $next++;
            endforeach;
            $next++;
        endforeach;

        $sheet->setCellValue('E'.$next, Generals::getTitle('EXPORT_TOTAL'));
        $sheet->setCellValue('F'.$next, '');
        $sheet->setCellValue('G'.$next, '');
        $ranges = json_decode($service['perpax']);
        if (is_array($ranges)) foreach ($ranges as $key => $pax):
            $column = $this->getColumnExcel($key+7);
            $sheet->setCellValue($column.$next, '=SUM('.$column.'4:'.$column.($next-2).')');
        endforeach;
        $column = $this->getColumnExcel($key+8);
        $sheet->setCellValue($column.$next, '=SUM('.$column.'4:'.$column.($next-2).')');
        $sheet->getStyle('A'.$next.':'.$column.$next)->applyFromArray(array('font' => array('bold' => true, 'color' => array('rgb' => 'FF0000'))));
        $next++;

        $sheet->setCellValue('E'.$next, Generals::getTitle('EXPORT_MARKUP'));
        #$sheet->setCellValue('F'.$next, $quotation['markup'].'%')->getStyle('F'.$next)->applyFromArray(array('alignment'=>array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)));
        $sheet->setCellValue('G'.$next, '');
        $ranges = json_decode($service['perpax']);
        if (is_array($ranges)) foreach ($ranges as $key => $pax):
            $column = $this->getColumnExcel($key+7);
            #$sheet->setCellValue($column.$next, '='.$column.($next-1).'*F'.$next);
            $sheet->setCellValue($column.$next, $markup[$column]);
        endforeach;
        $column = $this->getColumnExcel($key+8);
        #$sheet->setCellValue($column.$next, '='.$column.($next-1).'*F'.$next);
        $sheet->setCellValue($column.$next, $markup[$column]);
        $sheet->getStyle('A'.$next.':'.$column.$next)->applyFromArray(array('font' => array('bold' => true, 'color' => array('rgb' => 'FF0000'))));
        $next++;

        $sheet->setCellValue('E'.$next, Generals::getTitle('EXPORT_ADULT'));
        $sheet->setCellValue('F'.$next, '');
        $sheet->setCellValue('G'.$next, '');
        $ranges = json_decode($service['perpax']);
        if (is_array($ranges)) foreach ($ranges as $key => $pax):
            $column = $this->getColumnExcel($key+7);
            $sheet->setCellValue($column.$next, '='.$column.($next-2).'+'.$column.($next-1));
        endforeach;
        $column = $this->getColumnExcel($key+8);
        $sheet->setCellValue($column.$next, '='.$column.($next-2).'+'.$column.($next-1));
        $sheet->getStyle('A'.$next.':'.$column.$next)->applyFromArray(array('font' => array('bold' => true, 'color' => array('rgb' => 'FF0000'))));
        $next++;

        $sheet->setCellValue('E'.$next, Generals::getTitle('EXPORT_CHILD'));
        #$sheet->setCellValue('F'.$next, ceil(100-$quotation['discount']).'%')->getStyle('F'.$next)->applyFromArray(array('alignment'=>array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)));
        $sheet->setCellValue('G'.$next, '');
        $ranges = json_decode($service['perpax']);
        if (is_array($ranges)) foreach ($ranges as $key => $pax):
            if ($pax->range > 1):
                $column = $this->getColumnExcel($key+7);
                #$sheet->setCellValue($column.$next, '='.$column.($next-1).'*F'.$next);
                $sheet->setCellValue($column.$next, $discount[$column]);
            endif;
        endforeach;
        $column = $this->getColumnExcel($key+8);
        #$sheet->setCellValue($column.$next, '='.$column.($next-1).'*F'.$next);
        $sheet->setCellValue($column.$next, $discount[$column]);
        $sheet->getStyle('A'.$next.':'.$column.$next)->applyFromArray(array('font' => array('bold' => true, 'color' => array('rgb' => 'FF0000'))));
        $next++;

        $sheet->setCellValue('E'.$next, Generals::getTitle('EXPORT_SINGLE'));
        $sheet->setCellValue('F'.$next, '');
        $sheet->setCellValue('G'.$next, '');
        $ranges = json_decode($service['perpax']);
        if (is_array($ranges)) foreach ($ranges as $key => $pax):
            if ($pax->range > 1):
                $column = $this->getColumnExcel($key+7);
                $islast = $sheet->getHighestDataColumn();
                $sheet->setCellValue($column.$next, '='.$column.($next-2).'+'.$islast.($next-2));
            endif;
        endforeach;
        $sheet->getStyle('A'.$next.':'.$column.$next)->applyFromArray(array('font' => array('bold' => true, 'color' => array('rgb' => 'FF0000'))));
        #==============================================================================================
        #==============================================================================================
        $column = $this->getColumnExcel(count($ranges)+7);
        $sheet->getStyle('F5:'.$column.$next)->getNumberFormat()->setFormatCode('#,##0.00');

        foreach (range('A', $sheet->getHighestDataColumn()) as $col):
            $sheet->getColumnDimension($col)->setAutoSize(true);
            #$width = $sheet->getColumnDimension($col)->getWidth();
            #$sheet->getColumnDimension($col)->setWidth((int) $width * 1.05);
        endforeach;
        $sheet->calculateColumnWidths();
        $sheet->setTitle($quotation['code']);
        $objPHPExcel->setActiveSheetIndex(0);

        if ($save):
            $ExportPath = IMG_PATH.DS."export";
            if (!JFolder::exists($ExportPath)){ JFolder::create($ExportPath); }
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save($ExportPath.DS.$quotation['code'].'.xlsx');
            return $ExportPath.DS.$quotation['code'].'.xlsx';
        else:
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$quotation['code'].'.xlsx"');
            header('Cache-Control: max-age=0');
            header('Cache-Control: max-age=1');
            header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
            header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header ('Pragma: public'); // HTTP/1.0

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save('php://output');
            exit;
        endif;
    }

    function getColumnExcel($key){
        $alphabet = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
        return $alphabet[$key];
    }

    function setConfirmService() {
        $sid = Generals::getVar('sid');
        $val = Generals::getVar('val');
        $this->_db->update_db('tbl_journey_service', array('status' => (int)$val), ' id = ? ', array((int)$sid));
    }
}
?>
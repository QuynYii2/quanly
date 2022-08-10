<?php
class controler {
	var $_db;
    var $_genre;
    var $_module;
    var $_permission;

	function __construct() {
        $this->_genre = Generals::getVar('option', 'reservation');
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
		Generals::redirect('index.php?option='.$this->_module.'&view=list');
	}

	function delete() {
        if (empty($this->_permission['mod_delete'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        $cid = Generals::getVar('cid', array());
		if (is_array($cid)) foreach ($cid as $val) $this->_db->delete($val);
		Generals::setError(str_replace('%s', count($cid), Generals::getTitle('DELETES_SUCCESS')));
		Generals::redirect('index.php?option='.$this->_module.'&view=list');
	}

	function create() {
        if (empty($this->_permission['mod_create'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        header('location: index.php?option='.$this->_module.'&view=form');
		return false;
	}

	function setPending() {
        #if (empty($this->_permission['mod_update'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
            return;
        #endif;

        $cid = Generals::getVar('cid', array());
		$this->_db->published(0, $cid);
		Generals::setError(str_replace('%s', count($cid), Generals::getTitle('MESS_PENDING_SUCCESS')));
		Generals::redirect('index.php?option='.$this->_module.'&view=list');
	}

    function setReservation() {
        #if (empty($this->_permission['mod_update'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
            return;
        #endif;

        $cid = Generals::getVar('cid', array());
        $this->_db->published(1, $cid);
        Generals::setError(str_replace('%s', count($cid), Generals::getTitle('MESS_RESERVATION_SUCCESS')));
        Generals::redirect('index.php?option='.$this->_module.'&view=list');
    }

    function setOperation() {
        if (empty($this->_permission['mod_update'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        $cid = Generals::getVar('cid', array());
        $this->_db->published(2, $cid);
        Generals::setError(str_replace('%s', count($cid), Generals::getTitle('MESS_OPERATION_SUCCESS')));
        Generals::redirect('index.php?option='.$this->_module.'&view=list');
    }

    function setAccounting() {
        if (empty($this->_permission['mod_update'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        $cid = Generals::getVar('cid', array());
        $this->_db->published(3, $cid);
        Generals::setError(str_replace('%s', count($cid), Generals::getTitle('MESS_ACCOUNTING_SUCCESS')));
        Generals::redirect('index.php?option='.$this->_module.'&view=list');
    }

    function setCancel() {
        #if (empty($this->_permission['mod_update'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
            return;
        #endif;

        $cid = Generals::getVar('cid', array());
        $this->_db->published(4, $cid);
        Generals::setError(str_replace('%s', count($cid), Generals::getTitle('MESS_CANCEL_SUCCESS')));
        Generals::redirect('index.php?option='.$this->_module.'&view=list');
    }

    function prepare() {
        if (empty($this->_permission['mod_update'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        $cid = Generals::getVar('cid', array());
        $this->_db->setStatus(0, $cid);
        Generals::setError(str_replace('%s', count($cid), Generals::getTitle('STATUS_SUCCESS')));
        Generals::redirect('index.php?option='.$this->_module.'&view=list');
    }

    function starting() {
        if (empty($this->_permission['mod_update'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        $cid = Generals::getVar('cid', array());
        $this->_db->setStatus(1, $cid);
        Generals::setError(str_replace('%s', count($cid), Generals::getTitle('STATUS_SUCCESS')));
        Generals::redirect('index.php?option='.$this->_module.'&view=list');
    }

    function fnished() {
        if (empty($this->_permission['mod_update'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        $cid = Generals::getVar('cid', array());
        $this->_db->setStatus(2, $cid);
        Generals::setError(str_replace('%s', count($cid), Generals::getTitle('STATUS_SUCCESS')));
        Generals::redirect('index.php?option='.$this->_module.'&view=list');
    }

    function unmoney() {
        if (empty($this->_permission['mod_update'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        $cid = Generals::getVar('cid', array());
        $this->_db->setMoney(0, $cid);
        Generals::setError(str_replace('%s', count($cid), Generals::getTitle('MONEY_SUCCESS')));
        Generals::redirect('index.php?option='.$this->_module.'&view=list');
    }

    function ismoney() {
        if (empty($this->_permission['mod_update'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        $cid = Generals::getVar('cid', array());
        $this->_db->setMoney(1, $cid);
        Generals::setError(str_replace('%s', count($cid), Generals::getTitle('MONEY_SUCCESS')));
        Generals::redirect('index.php?option='.$this->_module.'&view=list');
    }

	function trash() {
        #if (empty($this->_permission['mod_delete'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
            return;
        #endif;

        $cid = Generals::getVar('cid', array());
		$this->_db->published(-1, $cid[0]);
	}

    function status() {
        $vform 	= Generals::getVar('vform', array());
        $cid	= Generals::getVar('cid', array(0));
        $vform['published'] = $vform['published'] > 3 ? 3 : $vform['published'];
        $vform['published'] = $vform['published'] < 2 ? 2 : $vform['published'];

        $this->_db->update_db('tbl_quotation', $vform, ' id = ? ', array($cid[0]));
        Generals::setError(Generals::getTitle('UPDATE_SUCCESS'));
        Generals::redirect('index.php?option='.$this->_module.'&view=list');
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
            $person = $this->_db->getPerson($vform['paxgroup']);
            $agency = $this->_db->getAgencies(implode(',', $vform['agencies']));

            $vform['genre'] = $this->_genre;
            $vform['agency'] = $vform['agencies'][0];
            $vform['agencies'] = !empty($vform['agencies']) ? ','.implode(',', $vform['agencies']).',' : null;
            $vform["departure"] = $vform["departure"] ? date('Y-m-d H:i:s', strtotime($vform["departure"])) : null;
            $vform['discount'] = (float)$vform['discount'];
            $vform['paxrange'] = $person['divpax'];
            $vform['code'] = $vform['code'] ? $vform['code'] : $agency[0]['code'].date('ym').$this->_db->getMaxCode();

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
                $DiskPath = IMG_PATH.DS."extquotation";
                if (JFile::exists($DiskPath.DS.Generals::getVar('hiddensocial'))) JFile::delete($DiskPath.DS.Generals::getVar('hiddensocial'));
                $vform['social'] = '';
            endif;

            $file_social = $_FILES['social'];

            if($file_social['name'] != '')  {
                if (!Generals::addFile('social', 'normal', IMG_PATH.DS."extquotation")) {
                    $link= 'index.php?option='.$this->_module.'&view=form&cid[]='. $vform['id'];
                    Generals::redirect($link); return;
                } else {
                    $vform['social'] = Generals::getVar('social');
                }
            }
            #=========================================================================================
            #=========================================================================================
            $data = $this->_db->getDataMapTable($vform, 'tbl_quotation');
            $data['published'] = $data['published'] > 3 ? 3 : $data['published'];
            $data['published'] = $data['published'] < 2 ? 2 : $data['published'];

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
                    $link = 'index.php?option='.$this->_module.'&view=list';
                elseif ($task == "apply"):
                    $link = 'index.php?option='.$this->_module.'&view=form&cid[]='. $vform['id'] .'';
                endif;
            } catch (Exception $ex) {
                $this->_db->rollbackTrans();
                Generals::setError(Generals::getTitle('UPDATE_EROR'));
                $link = 'index.php?option='.$this->_module.'&view=form&cid[]='. $vform['id'] .'';
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
                $link = 'index.php?option='.$this->_module.'&view=form&cid[]='. $vform['id'] .'';
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
                    $isedit     = false;

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
                        else:
                            $journey = $journey_id;
                        endif;
                        #========================================================
                        if (is_array($service)):
                            foreach ($detail as $i => $temp2):
                                if (!empty($temp2)):
                                    if (empty($service[$i])):
                                        $servergenre = $this->_db->getServiceData($profile[$i]);
                                        $service[$i] = $servergenre['genre'];
                                    endif;
                                    $convert= number_format($conversion[$i] ? $conversion[$i] : ($vform['conversion'] ? $vform['conversion'] : 1), 2);
                                    $prices = $this->getPrice($service[$i], $detail[$i], $convert, $price[$i], $vform['paxgroup'], $vform['saleof'], $vform['pricefor']);
                                    $fnames = $this->_db->getLocations($f_location);
                                    $tnames = $this->_db->getLocations($t_location);

                                    $data = array();
                                    $data['quotation']  = $vform['id'];
                                    $data['journey']    = $journey;
                                    $data['service']    = $service[$i];
                                    $data['location']   = (int)$location[$i];
                                    $data['profile']    = (int)$profile[$i];
                                    $data['detail']     = (int)$detail[$i];
                                    $data['markup']     = number_format($prices['markup'], 2, '.', '');
                                    $data['discount']   = number_format($vform['discount'], 2, '.', '');
                                    $data['conversion'] = number_format($conversion[$i] ? $conversion[$i] : ($vform['conversion'] ? $vform['conversion'] : 1), 2, '.', '');
                                    $data['price']      = number_format($prices['cost'], 2, '.', '');
                                    $data['single']     = number_format($prices['single'], 2, '.', '');
                                    $data['perpax']     = number_format($prices['perpax'], 2, '.', '');
                                    $data['fromname']   = $fnames[0]['name'];
                                    $data['toname']     = $tnames[0]['name'];
                                    $data['detailname'] = $prices['name'];
                                    $data['detailcode'] = $prices['code'];

                                    if (!$service_id[$i]):
                                        $this->_db->insert_db('tbl_journey_service', $data);
                                    else:
                                        $this->_db->update_db('tbl_journey_service', $data, ' id = ? ', array($service_id[$i]));
                                    endif;
                                endif;
                            endforeach;
                        endif;
                    endif;
                endforeach;
                if ($isedit) $this->_db->update_db('tbl_quotation', array('journeis' => implode(' - ', $journeis)), ' id = ? ', array($vform['id']));
                $this->_db->commitTrans();
            } catch (Exception $ex) {
                $this->_db->rollbackTrans();
                Generals::setError(Generals::getTitle('UPDATE_EROR'));
                $link = 'index.php?option='.$this->_module.'&view=form&cid[]='. $vform['id'] .'';
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
		Generals::redirect('index.php?option='.$this->_module.'&view=list');
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
        $genre      = Generals::getVar('genre');
        $parent     = Generals::getVar('parent');
        $setvalue   = Generals::getVar('setvalue');
        $arrival    = Generals::getVar('arrival');
        $today      = Generals::getVar('today');
        $paxgroup   = Generals::getVar('paxgroup');
        $arrival    = date('Y-m-d', strtotime('+'.$today.' day', strtotime($arrival)));

        if (empty($genre)):
            $service = $this->_db->getServiceData($parent);
            $genre   = $service['genre'];
        endif;

        $result = $this->_db->getSubServices($genre, $parent, $arrival, $paxgroup);
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
        $person = (int)Generals::getVar('person', 1);
        $saleof = (int)Generals::getVar('saleof', 0); # 0: Discount after - 1: Discount before
        $convert= Generals::getVar('convert', 1);
        $convert= $convert ? str_replace(',', '', $convert) : 0;
        $convert= $convert ? $convert : 1;
        $price  = $price ? str_replace(',', '', $price) : 0;
        $issing = (int)Generals::getVar('issing', 0);

        if (empty($genre)):
            $service = $this->_db->getServiceData($parent);
            $genre   = $service['genre'];
        endif;

        $result = $this->_db->getDetailService($genre, $subid);
        $person = $this->_db->getPerson($person);

        #if ($price > 0 && is_numeric($price)):
        #    $result['cost'] = $price / $convert;
        #    $result['single'] = $result['single'] + $result['single']*$result['margin']/100;
        #else:
            $result['cost']     = $result['cost'] + $result['cost']*$result['margin']/100;
            $result['extra']    = ($genre == 'hotel' && $person['maxpax']%$result['perpax']) ? $result['extra'] + $result['extra']*$result['margin']/100 : 0;
            $result['single']   = $result['single'] + $result['single']*$result['margin']/100;
        #endif;

        $return = '<tr class="bg-info">\n';
        $return.= ' <td class="text-center prices">'.number_format($result['cost']*$convert, 2).'<input type="hidden" id="control-'.$day.'-'.$num.'-7" name="vdata[price]['.$day.']['.$num.']" field="price" class="form-control number price" value="'.number_format($result['cost']*$convert, 2).'"><input type="hidden" name="vdata[update]['.$day.']['.$num.']" class="update" value="1"/></td>';

        if ($result['perpax']):
            if ($saleof):
                if ($genre == 'hotel'):
                    $count = floor($person['maxpax']/$result['perpax']);
                    $_mins = floor($person['minpax']/$result['perpax']);
                    if ($issing == 2 && !empty($_mins)):
                        $price = number_format($result['single']*$convert, 2);
                        $single= number_format($result['single']*$convert, 2);
                    elseif ($issing || empty($_mins)):
                        $price = $_mins ? number_format(($result['cost']*$count)*$convert/($_mins*$result['perpax']), 2) : 0;
                        $single= number_format(($person['maxpax']%$result['perpax'])*$result['single']*$convert, 2);
                    else:
                        $price = number_format(($result['cost']*$count+$result['extra'])*$convert/$person['minpax'], 2);
                        $single= number_format(($person['maxpax']%$result['perpax'])*$result['extra']*$convert, 2);
                    endif;
                else:
                    $count = ceil($person['maxpax']/$result['perpax']);
                    $price = number_format(($result['cost']*$count)*$convert/$person['minpax'], 2);
                    $single= null;
                endif;
            else:
                if ($genre == 'hotel'):
                    $count = floor($person['maxpax']/$result['perpax']);
                    $_mins = floor($person['minpax']/$result['perpax']);
                    if ($issing == 2 && !empty($_mins)):
                        $price = number_format($result['single']*$convert, 2);
                        $single= number_format($result['single']*$convert, 2);
                    elseif ($issing || empty($_mins)):
                        $price = $_mins ? number_format(($result['cost']*$count)*$convert/($_mins*$result['perpax']), 2) : 0;
                        $single= number_format(($person['maxpax']%$result['perpax'])*$result['single']*$convert, 2);
                    else:
                        $price = number_format(($result['cost']*$count+$result['extra'])*$convert/$person['minpax'], 2);
                        $single= number_format(($person['maxpax']%$result['perpax'])*$result['extra']*$convert, 2);
                    endif;
                else:
                    $count = ceil($person['maxpax']/$result['perpax']);
                    $price = number_format(($result['cost']*$count)*$convert/$person['minpax'], 2);
                    $single= null;
                endif;
            endif;
        else:
            if ($saleof):
                $price = number_format(($result['cost']/$person['minpax'])*$convert, 2);
            else:
                $price = number_format(($result['cost']/$person['minpax'])*$convert, 2);
            endif;
        endif;
        $return.= ' <td class="text-center perpax">'.$price.'</td>';

        $return.= ' <td class="text-center single">'.$single.'</td>';
        $return.= '</tr>\n';

        echo $return;
    }

    function getPrice($genre, $subid, $convert = 1, $price = 0, $paxgroup = 0, $saleof = 0, $issing = 0) {
        $convert= $convert ? $convert : 1;
        $result = $this->_db->getDetailService($genre, $subid);
        $person = $this->_db->getPerson($paxgroup);
        $price  = $price ? str_replace(',', '', $price) : 0;
        $return = array();
        $return['code']     = $result['code'];
        $return['name']     = $result['name'];
        $return['single']   = $result['single']*$convert;

        #if ($price > 0 && is_numeric($price)):
        #    $result['cost']     = $price / $convert;
        #    $return['cost']     = $result['cost']*$convert;
        #    $return['markup']   = 0;
        #else:
            $result['cost']     = $result['cost'];
            $result['extra']    = ($genre == 'hotel' && $person['maxpax']%$result['perpax']) ? $result['extra'] : 0;
            $result['single']   = $result['single'];

            $return['cost']     = $result['cost']*$convert;
            $return['markup']   = $result['margin'];
        #endif;

        if ($result['perpax']):
            if ($saleof):
                if ($genre == 'hotel'):
                    $count = floor($person['maxpax']/$result['perpax']);
                    $_mins = floor($person['minpax']/$result['perpax']);
                    if ($issing == 2 && !empty($_mins)):
                        $return['perpax'] = number_format($result['single']*$convert, 2);
                        $return['single'] = number_format($result['single']*$convert, 2);
                    elseif ($issing || empty($_mins)):
                        $return['perpax'] = $_mins ? number_format(($result['cost']*$count)*$convert/($_mins*$result['perpax']), 2) : 0;
                        $return['single'] = number_format(($person['maxpax']%$result['perpax'])*$result['single']*$convert, 2);
                    else:
                        $return['perpax'] = number_format(($result['cost']*$count+$result['extra'])*$convert/$person['minpax'], 2);
                        $return['single'] = number_format(($person['maxpax']%$result['perpax'])*$result['extra']*$convert, 2);
                    endif;
                else:
                    $count = ceil($person['maxpax']/$result['perpax']);
                    $return['perpax'] = number_format(($result['cost']*$count)*$convert/$person['minpax'], 2);
                    $return['single'] = null;
                endif;
            else:
                if ($genre == 'hotel'):
                    $count = floor($person['maxpax']/$result['perpax']);
                    $_mins = floor($person['minpax']/$result['perpax']);
                    if ($issing == 2 && !empty($_mins)):
                        $return['perpax'] = number_format($result['single']*$convert, 2);
                        $return['single'] = number_format($result['single']*$convert, 2);
                    elseif ($issing || empty($_mins)):
                        $return['perpax'] = $_mins ? number_format(($result['cost']*$count)*$convert/($_mins*$result['perpax']), 2) : 0;
                        $return['single'] = number_format(($person['maxpax']%$result['perpax'])*$result['single']*$convert, 2);
                    else:
                        $return['perpax'] = number_format(($result['cost']*$count+$result['extra'])*$convert/$person['minpax'], 2);
                        $return['single'] = number_format(($person['maxpax']%$result['perpax'])*$result['extra']*$convert, 2);
                    endif;
                else:
                    $count = ceil($person['maxpax']/$result['perpax']);
                    $return['perpax'] = number_format(($result['cost']*$count)*$convert/$person['minpax'], 2);
                    $return['single'] = null;
                endif;
            endif;
        else:
            if ($saleof):
                $return['perpax'] = number_format(($result['cost']/$person['minpax'])*$convert, 2);
                $return['single'] = null;
            else:
                $return['perpax'] = number_format(($result['cost']/$person['minpax'])*$convert, 2);
                $return['single'] = null;
            endif;
        endif;

        return $return;
    }

    function export(){ $this->save('apply', 'export'); }

    function sendto(){ $this->save('apply', 'sendto'); }

    function export2excel($qid, $save = false){
        $quotation   = $this->_db->getData($qid);
        $journeies   = $this->_db->getJourneies($qid);
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
            ->setLastModifiedBy("Maarten Balliauw")
            ->setTitle("PHPExcel Test Document")
            ->setSubject("PHPExcel Test Document")
            ->setDescription("Test document for PHPExcel, generated using PHP classes.")
            ->setKeywords("office PHPExcel php")
            ->setCategory("Test result file");

        # Line First Empty
        $first = 8;
        $next  = 8;
        $sheet = $objPHPExcel->setActiveSheetIndex(0);
        $sheet->getDefaultStyle()->applyFromArray(array('font'=>array('size'=>10)));

        $objDrawing = new PHPExcel_Worksheet_Drawing();
        $objDrawing->setName("VisitAsia");
        $objDrawing->setDescription("VisitAsia");
        $objDrawing->setPath(DATA_PATH.'images'.DS.'export-logo.png');
        $objDrawing->setHeight(100);
        $objDrawing->setCoordinates('A1');
        $objDrawing->setWorksheet($sheet);

        $sheet->setCellValue('D1', Generals::getTitle('MENU_EXTQUOTATION').': '.$quotation['code'])->mergeCells('D1:I'.($next-2));
        $sheet->getStyle('D1:I'.($next-2))->applyFromArray(array('font' => array('size' => 20), 'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER)));

        $sheet->setCellValue('A'.$next, Generals::getTitle('EXPORT_DAYS'));
        $sheet->setCellValue('B'.$next, Generals::getTitle('EXPORT_FROM'));
        $sheet->setCellValue('C'.$next, Generals::getTitle('EXPORT_TO'));
        $sheet->setCellValue('D'.$next, Generals::getTitle('EXPORT_SERVIE'));
        $sheet->setCellValue('E'.$next, Generals::getTitle('EXPORT_CODE'));
        $sheet->setCellValue('F'.$next, Generals::getTitle('EXPORT_INTRO'));
        $sheet->setCellValue('G'.$next, Generals::getTitle('EXPORT_PRICE'));
        $sheet->setCellValue('H'.$next, Generals::getTitle('EXPORT_PERPAX'));
        $sheet->setCellValue('I'.$next, Generals::getTitle('EXPORT_SINGLE'));
        $sheet->freezePaneByColumnAndRow(0, $next+1);
        $sheet->getStyle('A'.$next.':I'.$next)->applyFromArray(array('font' => array('bold' => true)));
        $next++;
        #==============================================================================================
        #==============================================================================================
        $total  = array();
        $markup = array();
        $totals = 0;
        if (is_array($journeies)) foreach ($journeies as $day => $journey):
            $sheet->setCellValue('A'.$next, Generals::getTitle('EXPORT_DAY').' '.($day+1));
            $sheet->setCellValue('B'.$next, $journey['f_name']);
            $sheet->setCellValue('C'.$next, $journey['t_name']);
            if (is_array($journey['services'])) foreach ($journey['services'] as $service):
                $profile = $this->_db->getServiceData($service['profile']);
                $sheet->setCellValue('D'.$next, Generals::getTitle('EXPORT_SERVICE_'.strtoupper($service['service'])));
                $sheet->setCellValue('E'.$next, $service['detailcode']);
                $sheet->setCellValue('F'.$next, $profile['name'].' - '.$service['detailname']);
                $sheet->setCellValue('G'.$next, ($service['price']+$service['price']*$service['markup']/100))->getStyle('G'.$next)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->setCellValue('H'.$next, ($service['perpax']+$service['perpax']*$service['markup']/100))->getStyle('H'.$next)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->setCellValue('I'.$next, ($service['single']+$service['single']*$service['markup']/100))->getStyle('I'.$next)->getNumberFormat()->setFormatCode('#,##0.00');

                $total['H'] += $service['perpax']+$service['perpax']*$service['markup']/100;
                $total['I'] += $service['single']+$service['single']*$service['markup']/100;
                $markup['H']+= ($service['perpax']*$service['markup'])/100;
                $markup['I']+= ($service['single']*$service['markup'])/100;

                if ($service['service'] == 'hotel' && $quotation['pricefor'] == 2):
                    $totals+= ($service['perpax']+$service['perpax']*$service['markup']/100)*$quotation['paxrange'];
                elseif ($service['service'] == 'hotel' && ($quotation['pricefor'] || $quotation['paxrange'] == 1)):
                    $totals+= ($service['perpax']+$service['perpax']*$service['markup']/100)*floor($quotation['paxrange']/2)*2 + $service['single']+$service['single']*$service['markup']/100;
                else:
                    $totals+= ($service['perpax']+$service['perpax']*$service['markup']/100)*$quotation['paxrange'];
                endif;

                $next++;
            endforeach;
            $next++;
        endforeach;

        $sheet->setCellValue('G'.$next, Generals::getTitle('EXPORT_MARKUP'));
        $sheet->setCellValue('H'.$next, $markup['H'])->getStyle('H'.$next)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->setCellValue('I'.$next, $markup['I'])->getStyle('I'.$next)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('G'.$next.':I'.$next)->applyFromArray(array('font' => array('bold' => true, 'color' => array('rgb' => 'FF0000'))));
        $next++;

        $sheet->setCellValue('G'.$next, Generals::getTitle('EXPORT_TOTAL'));
        $sheet->setCellValue('H'.$next, '=SUM(H'.$first.':H'.($next-2).')')->getStyle('H'.$next)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->setCellValue('I'.$next, '=SUM(I'.$first.':I'.($next-2).')')->getStyle('I'.$next)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('G'.$next.':I'.$next)->applyFromArray(array('font' => array('bold' => true, 'color' => array('rgb' => 'FF0000'))));
        $next++;

        if ($quotation['disgenre']):
            $_total = $totals - $quotation['discount'];
        else:
            $_total = $totals - number_format($totals*$quotation['discount']/100, 2, '.', ',');
        endif;

        $next++;
        $sheet->setCellValue('A'.$next, Generals::getTitle('EXPORT_DISCOUNT'))->mergeCells('A'.$next.':H'.$next);
        $sheet->setCellValue('I'.$next, !empty($quotation['disgenre']) ? $quotation['discount'] : $totals*$quotation['discount']/100)->getStyle('I'.$next)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('A'.$next.':I'.$next)->applyFromArray(array('font' => array('size'=>14, 'bold' => true, 'color' => array('rgb' => 'FF0000')), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)));
        $next++;

        $sheet->setCellValue('A'.$next, Generals::getTitle('EXPORT_WITH_DISCOUNT'))->mergeCells('A'.$next.':H'.$next);
        $sheet->setCellValue('I'.$next, $_total)->getStyle('I'.$next)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('A'.$next.':I'.$next)->applyFromArray(array('font' => array('size'=>14, 'bold' => true, 'color' => array('rgb' => 'FF0000')), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)));
        $next++;

        if ($quotation['guest'] > 0):
            $person = $quotation['adult']+$quotation['child'];
            $sheet->setCellValue('G'.$next, $quotation['guest'].' '.Generals::getTitle('EXPORT_FOC'));
            $sheet->setCellValue('H'.$next, '=H'.($next-2).'/'.$person)->getStyle('H'.$next)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('G'.$next.':I'.$next)->applyFromArray(array('font' => array('bold' => true, 'color' => array('rgb' => 'FF0000'))));
            $next++;

            $sheet->setCellValue('G'.$next, Generals::getTitle('EXPORT_TOTAL_FOC'));
            $sheet->setCellValue('H'.$next, '=H'.($next-3).'+H'.($next-1))->getStyle('H'.$next)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('G'.$next.':I'.$next)->applyFromArray(array('font' => array('bold' => true, 'color' => array('rgb' => 'FF0000'))));
        endif;
        #==============================================================================================
        #==============================================================================================

        foreach (range('A', $sheet->getHighestDataColumn()) as $col):
            $sheet->getColumnDimension($col)->setAutoSize(true);
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

    function setPaidService() {
        $sid = Generals::getVar('sid');
        $val = Generals::getVar('val');
        $this->_db->update_db('tbl_journey_service', array('ispaid' => (int)$val), ' id = ? ', array((int)$sid));
        /**
        $query = 'SELECT a.* FROM tbl_journey_service AS a WHERE a.quotation = ? ORDER BY id ASC';
        $this->_db->prepare($query);
        $result = $this->_db->exec(array($sid));
        foreach ($result as $items):
            if (empty($items['ispaid'])) return;
        endforeach;
        **/
    }

    function setTotalMarkup(){
        $id = Generals::getVar('id');
        $total = Generals::getVar('total');
        $profit = Generals::getVar('profit');
        $this->_db->update_db('tbl_quotation', array('total' => $total, 'profit' => $profit), ' id = ? ', array((int)$id));
    }

    function getLogService($service, $detail){
        $query = ' SELECT js.service, sl.name AS profile, sv.name AS detail, sp.name AS supplier ';
        $query.= ' FROM tbl_journey_service AS js INNER JOIN tbl_'.$service.'_lang AS sv ON js.detail = sv.'.$service;
        $query.= ' INNER JOIN tbl_service AS sr ON sr.id = js.profile ';
        $query.= ' INNER JOIN tbl_service_lang AS sl ON sl.service = js.profile ';
        $query.= ' INNER JOIN tbl_supplier_lang AS sp ON sp.supplier = sr.supplier ';
        $query.= ' LEFT JOIN tbl_language AS lg ON lg.code = sl.language AND lg.code = sv.language AND lg.code = sp.language ';
        $query.= ' WHERE js.service = ? AND js.detail = ? AND lg.code = ? ';
        $this->_db->prepare($query);
        $detail = $this->_db->exec(array($service, $detail, Generals::getSession('langcode', 'vn')));
        return $detail[0];
    }

    function setPaymentPart(){
        $user   = Generals::getUserData();
        $pid    = Generals::getVar('pid');
        $qid    = Generals::getVar('qid');
        $genre  = Generals::getVar('genre');
        $price  = Generals::getVar('price');
        $billno = Generals::getVar('billno');
        $profit = Generals::getVar('profit');
        $total  = Generals::getVar('total');
        $detail     = Generals::getVar('detail');
        $service    = Generals::getVar('service');
        $paytype    = Generals::getVar('payment');
        $introtext  = Generals::getVar('introtext');

        if ($genre == 'addnew'):
            $data['quotation']  = $qid;
            $data['price']      = number_format($price, 2, '.', '');
            $data['billno']     = $billno;
            $data['markup']     = number_format($price*$profit/$total, 2, '.', '');
            $data['profit']     = $profit;
            $data['total']      = $total;
            $data['introtext']  = $introtext;
            $data['service']    = $service;
            $data['detail']     = $detail;
            $data['payment']    = $paytype;
            $data['genre']      = 1;
            $data['published']  = 1;
            $data["create_on"]  = date('Y-m-d H:i:s');
            $data["create_by"]  = $user['id'];

            $this->_db->insert_db('tbl_quotation_payment', $data);
            #=======================================================================================
            # Write Log ============================================================================
            #=======================================================================================
            $desciption = $this->getLogService($service, $detail);
            $logdata    = array();
            $quotation  = $this->_db->getDataLang(Generals::getSession('langcode', 'vn'), $qid);
            $sv_text    = Generals::getTitle('EXPORT_SERVICE_'.strtoupper($service));
            $logdata['genre']       = 1;
            $logdata['userid']      = $user['id'];
            $logdata['quotation']   = $qid;
            $logdata['title']       = Generals::getTitle('TITLE_LOG_EXPENSE_PART_ADD');
            $logdata['shorttext']   = $quotation['name'];
            $logdata['introtext']   = '<div>'.sprintf(Generals::getTitle('PAYMENT_SERVICE'), $sv_text, '<strong>'.$desciption['profile'].'</strong> - '.$desciption['detail'], $desciption['supplier']).'</div>';
            $logdata['introtext']  .= '<div><strong>'.Generals::getTitle('PAYMENT_PRICE').'</strong>: '.number_format($price, 2, '.', '').'</div>';
            $logdata['introtext']  .= '<div><strong>'.Generals::getTitle('PAYMENT_NOTE').'</strong>: '.$introtext.'</div>';
            $logdata['data_text']   = json_encode($data);
            $logdata['create_on']   = date('Y-m-d H:i:s');
            $logdata['create_by']   = $user['id'];
            $this->_db->insert_db('tbl_quotation_log', $logdata);
        #=======================================================================================
        #=======================================================================================
        elseif ($genre == 'remove' && !empty($pid)):
            $this->_db->prepare('SELECT a.* FROM tbl_quotation_payment AS a WHERE a.genre = 1 AND a.id = ?');
            $data = $this->_db->exec(array($pid));

            $this->_db->delete_db('tbl_quotation_payment', ' id = ? ', array($pid));
            #=======================================================================================
            # Write Log ============================================================================
            #=======================================================================================
            $desciption = $this->getLogService($data[0]['service'], $data[0]['detail']);
            $logdata    = array();
            $quotation  = $this->_db->getDataLang(Generals::getSession('langcode', 'vn'), $data[0]['quotation']);
            $sv_text    = Generals::getTitle('EXPORT_SERVICE_'.strtoupper($service));
            $logdata['genre']       = 1;
            $logdata['userid']      = $user['id'];
            $logdata['quotation']   = $data[0]['quotation'];
            $logdata['title']       = Generals::getTitle('TITLE_LOG_EXPENSE_PART_DEL');
            $logdata['shorttext']   = $quotation['name'];
            $logdata['introtext']   = '<div>'.sprintf(Generals::getTitle('PAYMENT_SERVICE'), $sv_text, '<strong>'.$desciption['profile'].'</strong> - '.$desciption['detail'], $desciption['supplier']).'</div>';
            $logdata['introtext']  .= '<div><strong>'.Generals::getTitle('PAYMENT_PRICE').'</strong>: '.number_format($data[0]['price'], 2, '.', '').'</div>';
            $logdata['introtext']  .= '<div><strong>'.Generals::getTitle('PAYMENT_NOTE').'</strong>: '.$data[0]['introtext'].'</div>';
            $logdata['data_text']   = json_encode($data[0]);
            $logdata['create_on']   = date('Y-m-d H:i:s');
            $logdata['create_by']   = $user['id'];
            $this->_db->insert_db('tbl_quotation_log', $logdata);
            #=======================================================================================
            #=======================================================================================
        endif;

        $return     = '';
        $payments   = $this->_db->getPayments(1, $qid);

        foreach ($payments as $payment):
            $return.= '<tr>';
            $return.= '    <td class="bg-danger" colspan="5"><strong>'.$payment['title'].'</strong><strong>'.Generals::getTitle('PAYMENT_TOTAL').': '.number_format($payment['total'], 2, '.', '').'; '.Generals::getTitle('PAYMENT_PAID').': '.number_format($payment['paid'], 2, '.', '').'; '.Generals::getTitle('PAYMENT_BALANCE').': '.number_format($payment['unpaid'], 2, '.', '').'</strong></td>';
            $return.= '</tr>';
            foreach ($payment['value'] as $items):
                $return.= '<tr>';
                $return.= '    <td class="bg-warning" colspan="5" style="padding-left: 20px;"><strong>'.Generals::getTitle('EXPORT_SERVICE_'.strtoupper($items['service'])).'</strong> - '.$items['pr_name'].' - '.$items['dt_name'].' - <strong>'.Generals::getTitle('PAYMENT_TOTAL').': '.number_format($items['total'], 2, '.', '').'; '.Generals::getTitle('PAYMENT_PAID').': '.number_format($items['paid'], 2, '.', '').'; '.Generals::getTitle('PAYMENT_BALANCE').': '.number_format($items['unpaid'], 2, '.', '').'</strong></td>';
                $return.= '</tr>';
                foreach ($items['payment'] as $paid):
                    $return.= '<tr>';
                    $return.= '    <td style="padding-left: 40px;">'.$paid['create_on'].'</td>';
                    $return.= '    <td>'.number_format($paid['price'], 2, '.', '').'</td>';
                    $return.= '    <td>'.number_format($paid['total'], 2, '.', '').'</td>';
                    $return.= '    <td>'.$paid['introtext'].'</td>';
                    $return.= '    <td class="text-right"><a class="remove glyphicon glyphicon-remove-sign text-danger" href="javascript:;" pid="'.$paid['id'].'"></a></td>';
                    $return.= '</tr>';
                endforeach;
                #=========================================
                # Auto Set Supplier Payment ==============
                #=========================================
                if ($items['total'] <= $items['paid']):
                    $this->_db->update_db('tbl_journey_service', array('ispaid' => 1), ' detail = ? AND quotation = ? ', array((int)$items['detail'], (int)$items['id']));
                endif;
                #=========================================
                #=========================================
            endforeach;
        endforeach;
        #=========================================
        #=========================================
        $changelogs = $this->_db->getChangeLogs($logdata['quotation'], 1);
        $loglist = '<table class="table table-striped">';
        foreach ($changelogs as $items):
            $loglist.= '    <tr>';
            $loglist.= '        <td>';
            $loglist.= '            <h4>'.$items['title'].' - <small>'.$items['shorttext'].'</small></h4>';
            $loglist.= '            <h5>'.$items['user_name'].' - <small>'.$items['create_on'].'</small></h5>';
            $loglist.= '            <div>'.$items['introtext'].'</div>';
            $loglist.= '        </td>';
            $loglist.= '    </tr>';
        endforeach;
        $loglist.= '</table>';

        $select = '';
        foreach ($payments as $payment):
            $select.= '<optgroup label="'.$payment['title'].' ['.Generals::getTitle('PAYMENT_TOTAL').': '.number_format($payment['total'], 2, '.', '').'; '.Generals::getTitle('PAYMENT_BALANCE').': $'.number_format($payment['unpaid'], 2, '.', '').']">';
            foreach ($payment['value'] as $items):
                $select.= ' <option value="'.$items['detail'].'" total="'.$items['total'].'" profit="0" service="'.$items['service'].'">'.Generals::getTitle('EXPORT_SERVICE_'.strtoupper($items['service'])).' - '.$items['pr_name'].' - '.$items['dt_name'].' - '.Generals::getTitle('PAYMENT_TOTAL').': $'.number_format($items['total'], 2, '.', '').' - '.Generals::getTitle('PAYMENT_BALANCE').': $'.number_format($items['total']-$items['paid'], 2, '.', '').'</option>';
            endforeach;
            $select.= '</optgroup>';
        endforeach;

        echo json_encode(array('html'=>$return, 'logs'=>$loglist, 'select'=>$select));
    }
}
?>
<?php
if (!defined("WRITELOG_DAO_INC")) {
    define("WRITELOG_DAO_INC",1);

    class WritelogDao extends DbConnect /*DbMySQLConnect*/  {
        var $_id;

        function __construct() {
            $cid = Generals::getVar('cid', array());
            $this->setState();
            $this->setId($cid[0]);
            parent::__construct();
        }

        function setId($id) {
            $this->_id	= $id;
        }

        function setState() {
            if (!is_null(Generals::getVar('filter_search'))) 	Generals::setState('filter.search', strtolower(trim(Generals::getVar('filter_search'))));
            if (!is_null(Generals::getVar('filter_f_date')))    Generals::setState('filter.f_date', Generals::getVar('filter_f_date'));
            if (!is_null(Generals::getVar('filter_t_date')))    Generals::setState('filter.t_date', Generals::getVar('filter_t_date'));
            if (!is_null(Generals::getVar('filter_userid')))    Generals::setState('filter.userid', Generals::getVar('filter_userid'));
            if (!is_null(Generals::getVar('page'))) 			Generals::setState('page', Generals::getVar('page', 1));
            if (!is_null(Generals::getVar('orderby_order'))) 	Generals::setState('orderby.order', Generals::getVar('orderby_order', 'ASC'));
            if (!is_null(Generals::getVar('orderby_field'))) 	Generals::setState('orderby.field', Generals::getVar('orderby_field', 'a.id'));
        }

        function getState() {
            return array(
                'filter_search' => Generals::getState('filter.search'), 'filter_f_date' => Generals::getState('filter.f_date'),
                'orderby_order' => Generals::getState('orderby.order'), 'orderby_field' => Generals::getState('orderby.field'),
                'filter_t_date' => Generals::getState('filter.t_date'), 'filter_userid' => Generals::getState('filter.userid')
            );
        }

        function getUsers(){
            $query = ' SELECT a.id, b.name FROM tbl_user AS a ';
            $query.= ' LEFT JOIN tbl_user_lang AS b ON a.id = b.user';
            $query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
            $query.= ' WHERE b.language = ? ';
            $query.= ' ORDER BY a.id, b.name ';

            $this->prepare($query);
            $result = $this->exec(array(Generals::getSession('langcode')));

            return is_array($result) ? $result : array();
        }

        function _buildQuery(){
            $search 	= Generals::getState('filter.search');
            $f_date 	= Generals::getState('filter.f_date');
            $t_date 	= Generals::getState('filter.t_date');
            $userid 	= Generals::getState('filter.userid');
            $search 	= JString::strtolower($search);
            $params 	= array(Generals::getSession('langcode'));

            if (strpos($search, '"') !== false) $search = str_replace(array('=', '<'), '', $search);

            $where[] = ' u.language = ? ';
            if (strlen($search)):
                $where[] = ' (LOWER(a.title) LIKE ? OR LOWER(a.introtext) LIKE ?) ';
                array_push($params, '%'.strtolower($search).'%', '%'.strtolower($search).'%');
            endif;
            if (strlen($f_date)):
                $where[] = ' DATE_FORMAT(a.create_on, "%Y%m%d") >= ? ';
                array_push($params, date('Ymd', strtotime($f_date)));
            endif;
            if (strlen($t_date)):
                $where[] = ' DATE_FORMAT(a.create_on, "%Y%m%d") <= ? ';
                array_push($params, date('Ymd', strtotime($t_date)));
            endif;
            if (strlen($userid)):
                $where[] = ' a.create_by = ? ';
                array_push($params, $userid);
            endif;

            $where = ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
            $query = ' SELECT a.*, u.name AS user_name FROM tbl_writelog AS a ';
            $query.= ' LEFT JOIN tbl_user_lang AS u ON u.user = a.create_by '.$where;
            Generals::setState('params', $params);

            return $query;
        }

        function getDataList() {
            $field = Generals::getState('orderby.field', 'a.id') ? Generals::getState('orderby.field', 'a.id') : 'a.id';
            $order = Generals::getState('orderby.order', 'DESC') ? Generals::getState('orderby.order', 'DESC') : 'DESC';
            $query = $this->_buildQuery();
            $query.= ' ORDER BY '.$field.' '.$order;
            $this->prepare($query);
            $result = $this->exec(Generals::getState('params'));
            $this->total = sizeof($result);

            while (true):
                $offset = (int)(Generals::getState('page', 1)-1)*LIMIT_RECORD;
                if ($offset >= $this->total) {
                    $offset	= (int)$offset - (int)LIMIT_RECORD;
                    Generals::setState('page', Generals::getState('page', 1)-1);
                } elseif ($offset < 0) {
                    $offset = 0;
                    break;
                } else {
                    break;
                }
            endwhile;

            if (is_array($result)):
                $result = array_chunk($result, LIMIT_RECORD);
                $page	= Generals::getState('page', 1)-1 >= 0 ? Generals::getState('page', 1)-1 : 0;
            else:
                $page = 0;
                $result[0] = $result;
            endif;

            return $result[$page] ? $result[$page] : array();
        }

        function getCountData() {
            return $this->total;
        }

        public function delete($oid) {
            $this->delete_db('tbl_writelog', " id = ? ", array($oid));
        }
    }// end class
}
?>
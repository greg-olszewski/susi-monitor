<?php

class Data_model extends CI_Model
{

    public function __construct()
    {
        $this->load->database();
    }

    public function get_data()
    {
        $this->db->order_by('datetime', 'DESC');

        $dateLimitInThePast = date('U', strtotime('-1 hour'));
        //get a maximum 1 hour in the past condition here

        $query = $this->db->where('datetime >', $dateLimitInThePast)
            ->get('data');
        $result = $query->result_array();


        $resultSorted = array();

        foreach ($result as $res) {
            $resultSorted[$res['target_id']][] = $res;
        }

        return $resultSorted;
    }

    public function insert_check_data($tagetId, $status, $datetime)
    {
        $data = array(
            'target_id' => $tagetId,
            'status'  => $status,
            'datetime'  => $datetime,
        );

        return $this->db->insert('data', $data);
    }
}
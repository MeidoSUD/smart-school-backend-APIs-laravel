<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Stander_model extends MY_model
{

    public function add_domain($data)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        if (isset($data['id']) && $data['id'] != '') {
            $this->db->where('id', $data['id']);
            $query     = $this->db->update('domain', $data);
            $insert_id = $data['id'];
            $message   = UPDATE_RECORD_CONSTANT . " On domain id " . $insert_id;
            $action    = "Update";
            $record_id = $insert_id;
        } else {
            $this->db->insert('domain', $data);
            $insert_id = $this->db->insert_id();
            $message   = INSERT_RECORD_CONSTANT . " On domain id " . $insert_id;
            $action    = "Insert";
            $record_id = $insert_id;
        }

        $this->log($message, $record_id, $action);

        //======================Code End==============================

        $this->db->trans_complete(); # Completing transaction
        /* Optional */

        if ($this->db->trans_status() === false) {
            # Something went wrong.
            $this->db->trans_rollback();
            return false;
        } else {
            return $insert_id;
        }
    }

    public function getdomainBysubjectid($sub_id, $getdomainBysubjectid)
    {
        return $this->db->select('*')->from('domain')->where('subject_group_subject_id', $sub_id)->where('subject_group_class_sections_id', $getdomainBysubjectid)->get()->result_array();
    }
    public function getstanderBydomainid($domain_id, $getdomainBysubjectid)
    {
        return $this->db->select('*')->from('domain_stander')->where('domain_id', $domain_id)->get()->result_array();
    }
    public function get_strand_Bydomainid($domain_id, $getdomainBysubjectid)
    {
        return $this->db->select('*')->from('strand')->where('domain_id', $domain_id)->get()->result_array();
    }
    public function getdomainBydomainid($domain_id)
    {
        return $this->db->select('*')->from('domain')->where('id', $domain_id)->get()->result_array();
    }

    public function getdomainBysubjectidedit($sub_id, $subject_group_class_sections_id)
    {
        return $this->db->select('*')->from('domain')->where('subject_group_subject_id', $sub_id)->where('subject_group_class_sections_id', $subject_group_class_sections_id)->get()->result_array();
    }

    public function get_subjectNameBySubjectGroupSubjectId($subject_group_subject_id)
    {
        return $this->db->select('*')->from('subject_group_subjects')->join("subjects", "subjects.id = subject_group_subjects.subject_id")->where('subject_group_subjects.id', $subject_group_subject_id)->get()->row_array();
    }

    public function getSyllabusById($id)
    {
        return $this->db->select('*')->from('subject_syllabus')->where('id', $id)->get()->row();
    }

    //=======================strand==========================

    public function add_strand($data)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        if (isset($data['id']) && $data['id'] != '') {
            $this->db->where('id', $data['id']);
            $this->db->update('strand', $data);

            $message   = UPDATE_RECORD_CONSTANT . " On strand id " . $data['id'];
            $insert_id = $data['id'];
            $action    = "Update";
            $record_id = $data['id'];
        } else {
            $this->db->insert('strand', $data);
            $insert_id = $this->db->insert_id();
            $message   = INSERT_RECORD_CONSTANT . " On strand id " . $insert_id;
            $action    = "Insert";
            $record_id = $insert_id;
        }

        $this->log($message, $record_id, $action);
        //======================Code End==============================

        $this->db->trans_complete(); # Completing transaction
        /* Optional */

        if ($this->db->trans_status() === false) {
            # Something went wrong.
            $this->db->trans_rollback();
            return false;
        } else {
            return $insert_id;
        }
    }
    /////////////-----------------stander----------------------////////////////////////
     public function add_stander($data)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        if (isset($data['id']) && $data['id'] != '') {
            $this->db->where('id', $data['id']);
            $this->db->update('domain_stander', $data);

            $message   = UPDATE_RECORD_CONSTANT . " On stander id " . $data['id'];
            $insert_id = $data['id'];
            $action    = "Update";
            $record_id = $data['id'];
        } else {
            $this->db->insert('domain_stander', $data);
            $insert_id = $this->db->insert_id();
            $message   = INSERT_RECORD_CONSTANT . " On stander id " . $insert_id;
            $action    = "Insert";
            $record_id = $insert_id;
        }

        $this->log($message, $record_id, $action);
        //======================Code End==============================

        $this->db->trans_complete(); # Completing transaction
        /* Optional */

        if ($this->db->trans_status() === false) {
            # Something went wrong.
            $this->db->trans_rollback();
            return false;
        } else {
            return $insert_id;
        }
    }
    //--------------------------------------------------------------------------------
    //insert outcome
    //--------------------------------------------------------------------------------
    public function add_outcome($data)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        if (isset($data['id']) && $data['id'] != '') {
            $this->db->where('id', $data['id']);
            $this->db->update('domain_outcome', $data);

            $message = UPDATE_RECORD_CONSTANT . " On outcome id " . $data['id'];
            $insert_id = $data['id'];
            $action = "Update";
            $record_id = $data['id'];
        } else {
            $this->db->insert('domain_outcome', $data);
            $insert_id = $this->db->insert_id();
            $message = INSERT_RECORD_CONSTANT . " On outcome id " . $insert_id;
            $action = "Insert";
            $record_id = $insert_id;
        }

        $this->log($message, $record_id, $action);
        //======================Code End==============================

        $this->db->trans_complete(); # Completing transaction
        /* Optional */

        if ($this->db->trans_status() === false) {
            # Something went wrong.
            $this->db->trans_rollback();
            return false;
        } else {
            return $insert_id;
        }
    }

    public function getstrandBydomainid($domainid, $session)
    {
        return $this->db->select('*')->from('strand')->where('domain_id', $domainid)->where('session_id', $session)->get()->result_array();
    }
    //------------------------------------------------------------------------------------------------
    //get outcome based on domain id
    //------------------------------------------------------------------------------------------------

    public function getoutcomeBydomainid($domainid, $session)
    {
        return $this->db->select('*')->from('domain_outcome')->where('domain_id', $domainid)->where('session_id', $session)->get()->result_array();
    }

    public function getOutcomeBySubject($subject_id)
    {
        return $this->db->select('*')->from('domain_outcome')->where('subject_id', $subject_id)->get()->result_array();
    }
    //------------------------------------------------------------------------------------------------
    //get stander based on domain id
    //------------------------------------------------------------------------------------------------
    

    public function getstrandByID($id)
    {
        $this->db->select('strand.*,subject_groups.name as sgname,subjects.name as subname,sections.section as sname,sections.id as sectionid,subject_groups.id as subjectgroupsid,subjects.id as subjectid,class_sections.id as csectionid,classes.class as cname,classes.id as classid,domain.name as domainname,domain.subject_group_class_sections_id,domain.subject_group_subject_id')->from('strand');    
  
        $this->db->join("domain", "domain.id = strand.domain_id");
        $this->db->join("subject_group_subjects", "subject_group_subjects.id = domain.subject_group_subject_id");
        $this->db->join("subject_groups", "subject_groups.id = subject_group_subjects.subject_group_id");
        $this->db->join("subjects", "subjects.id = subject_group_subjects.subject_id");
        $this->db->join("subject_group_class_sections", "subject_group_class_sections.id = domain.subject_group_class_sections_id", 'inner');
        $this->db->join("class_sections", "class_sections.id = subject_group_class_sections.class_section_id");
        $this->db->join("sections", "sections.id = class_sections.section_id");
        $this->db->join("classes", "classes.id = class_sections.class_id");
        $this->db->where('strand.id', $id);
        $query = $this->db->get();   
        return $query->row();       
    }

    public function getstrand($session, $id = null)
    {
        $this->db->select('strand.*,subject_groups.name as sgname,subjects.name as subname,sections.section as sname,sections.id as sectionid,subject_groups.id as subjectgroupsid,subjects.id as subjectid,class_sections.id as csectionid,classes.class as cname,classes.id as classid,domain.name as domainname,domain.subject_group_class_sections_id,domain.subject_group_subject_id')->from('strand');

        if ($id != null) {
            $this->db->where('strand.domain_id', $id);
        }
        $this->db->where('strand.session_id', $session);
        $this->db->join("domain", "domain.id = strand.domain_id");
        $this->db->join("subject_group_subjects", "subject_group_subjects.id = domain.subject_group_subject_id");
        $this->db->join("subject_groups", "subject_groups.id = subject_group_subjects.subject_group_id");
        $this->db->join("subjects", "subjects.id = subject_group_subjects.subject_id");
        $this->db->join("subject_group_class_sections", "subject_group_class_sections.id = domain.subject_group_class_sections_id", 'inner');
        $this->db->join("class_sections", "class_sections.id = subject_group_class_sections.class_section_id");
        $this->db->join("sections", "sections.id = class_sections.section_id");
        $this->db->join("classes", "classes.id = class_sections.class_id");
        $this->db->group_by("domain.subject_group_subject_id");
        $this->db->group_by("strand.domain_id");

        $query = $this->db->get();
        if ($id != null) {
            return $query->row_array();
        } else {
            return $query->result_array();
        }
    }

    //--------------------------------------------------------------------------
    //get outcome
    //--------------------------------------------------------------------------
    public function getoutcome($session, $id = null)
    {
        $this->db->select('domain_outcome.*,subject_groups.name as sgname,subjects.name as subname,sections.section as sname,sections.id as sectionid,subject_groups.id as subjectgroupsid,subjects.id as subjectid,class_sections.id as csectionid,classes.class as cname,classes.id as classid,domain.name as domainname,domain.subject_group_class_sections_id,domain.subject_group_subject_id')->from('domain_outcome');

        if ($id != null) {
            $this->db->where('domain_outcome.domain_id', $id);
        }
        $this->db->where('domain_outcome.session_id', $session);
        $this->db->join("domain", "domain.id = domain_outcome.domain_id");
        $this->db->join("subject_group_subjects", "subject_group_subjects.id = domain.subject_group_subject_id");
        $this->db->join("subject_groups", "subject_groups.id = subject_group_subjects.subject_group_id");
        $this->db->join("subjects", "subjects.id = subject_group_subjects.subject_id");
        $this->db->join("subject_group_class_sections", "subject_group_class_sections.id = domain.subject_group_class_sections_id", 'inner');
        $this->db->join("class_sections", "class_sections.id = subject_group_class_sections.class_section_id");
        $this->db->join("sections", "sections.id = class_sections.section_id");
        $this->db->join("classes", "classes.id = class_sections.class_id");
        $this->db->group_by("domain.subject_group_subject_id");
        $this->db->group_by("domain_outcome.domain_id");

        $query = $this->db->get();
        if ($id != null) {
            return $query->row_array();
        } else {
            return $query->result_array();
        }
    }

    public function deletestrand($id, $session)
    {
        $this->db->where("id", $id)->where("session_id", $session)->delete('strand');
    }

    public function deletestrandbulk($id, $session)
    {
        $this->db->where("domain_id", $id)->where("session_id", $session)->delete('strand');
    }

    public function changestrandStatus($data)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        $this->db->where('id', $data['id']);
        $query = $this->db->update('strand', $data);

        $message   = UPDATE_RECORD_CONSTANT . " On  strand id " . $data['id'];
        $action    = "Update";
        $record_id = $data['id'];
        $this->log($message, $record_id, $action);
        //======================Code End==============================

        $this->db->trans_complete(); # Completing transaction
        /* Optional */

        if ($this->db->trans_status() === false) {
            # Something went wrong.
            $this->db->trans_rollback();
            return false;
        } else {
            return true;
        }
    }

    //==========================syllabus============================
    public function add_syllabus($data)
    {
        if (isset($data['id']) && $data['id'] > 0) {
            $this->db->where('id', $data['id']);
            $this->db->update('subject_syllabus', $data);
            $insert_id = $data['id'];
            $message   = UPDATE_RECORD_CONSTANT . " On Subject Syllabus id " . $insert_id;
            $action    = "Update";
            $record_id = $insert_id;
            return $record_id;
        } else {
            $this->db->insert('subject_syllabus', $data);
            $insert_id = $this->db->insert_id();
            $message   = INSERT_RECORD_CONSTANT . " On Subject Syllabus id " . $insert_id;
            $action    = "Insert";
            $record_id = $insert_id;
            return $this->db->insert_id();
        }
    }

    public function update_syllabus($data)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        $this->db->where('id', $data['id']);
        $query = $this->db->update('subject_syllabus', $data);
        $message   = UPDATE_RECORD_CONSTANT . " On  Subject Syllabus id " . $data['id'];
        $action    = "Update";
        $record_id = $data['id'];
        $this->log($message, $record_id, $action);
        //======================Code End==============================
        $this->db->trans_complete(); # Completing transaction
        /* Optional */
        if ($this->db->trans_status() === false) {
            # Something went wrong.
            $this->db->trans_rollback();
            return false;
        } else {
            return true;
        }
    }

    public function get($session, $id = null, $subject_group_subject_id = null)
    {
        $this->db->select('domain.*,subject_groups.name as sgname,subjects.name as subname,subjects.code as subjects_code,sections.section as sname,sections.id as sectionid,subject_groups.id as subjectgroupsid,subjects.id as subjectid,class_sections.id as csectionid,classes.class as cname,classes.id as classid')->from('domain');

        if ($id != null) {
            $this->db->where('domain.subject_group_class_sections_id', $id);
        }
        if ($subject_group_subject_id != null) {
            $this->db->where('subject_group_subjects.id', $subject_group_subject_id);
        }

        $this->db->where('domain.session_id', $session);
        $this->db->join("subject_group_subjects", "subject_group_subjects.id = domain.subject_group_subject_id");
        $this->db->join("subject_groups", "subject_groups.id = subject_group_subjects.subject_group_id");
        $this->db->join("subjects", "subjects.id = subject_group_subjects.subject_id");
        $this->db->join("subject_group_class_sections", "subject_group_class_sections.id = domain.subject_group_class_sections_id", 'inner');
        $this->db->join("class_sections", "class_sections.id = subject_group_class_sections.class_section_id");
        $this->db->join("sections", "sections.id = class_sections.section_id");
        $this->db->join("classes", "classes.id = class_sections.class_id");
        $this->db->group_by("domain.subject_group_subject_id");
        $this->db->group_by("domain.subject_group_class_sections_id");
        $query = $this->db->get();
        if ($id != null) {
            return $query->row_array();
        } else {
            return $query->result_array();
        }
    }

    public function getsubject_group_class_sectionsId($class_id, $section_id, $subject_group_id,$session_id=NULL)
    {
        $session_id=IsNullOrEmptyString($session_id) ? $this->current_session :$session_id;
        $sql   = "SELECT subject_groups.name, subject_group_class_sections.* from subject_group_class_sections INNER JOIN class_sections on class_sections.id=subject_group_class_sections.class_section_id INNER JOIN subject_groups on subject_groups.id=subject_group_class_sections.subject_group_id WHERE class_sections.class_id=" . $this->db->escape($class_id) . " and class_sections.section_id=" . $this->db->escape($section_id) . " and subject_groups.id=" . $this->db->escape($subject_group_id) . "and subject_groups.session_id=" . $this->db->escape($session_id) . " ORDER by subject_groups.id DESC";
        $query = $this->db->query($sql);
        return $query->row_array();
    }
    //get domain base on subject id
    public function getdomain_subjectId($class_id, $section_id, $subject_group_id, $subject_id ,$session_id=NULL)
    {
        $session_id=IsNullOrEmptyString($session_id) ? $this->current_session :$session_id;
        $sql = "SELECT 
            domain.*, 
            subject_groups.name, 
            subject_group_class_sections.* 
        FROM 
            domain 
        INNER JOIN subject_group_subjects 
            ON subject_group_subjects.id = domain.subject_group_subject_id 
        INNER JOIN subject_group_class_sections 
            ON subject_group_class_sections.id = domain.subject_group_class_sections_id 
        INNER JOIN class_sections 
            ON class_sections.id = subject_group_class_sections.class_section_id 
        INNER JOIN subject_groups 
            ON subject_groups.id = subject_group_class_sections.subject_group_id 
        WHERE 
            class_sections.class_id = " . $this->db->escape($class_id) . " 
            AND class_sections.section_id = " . $this->db->escape($section_id) . " 
            AND subject_groups.id = " . $this->db->escape($subject_group_id) . " 
            AND subject_groups.session_id = " . $this->db->escape($session_id) . " 
            AND subject_group_subjects.subject_id = " . $this->db->escape($subject_id) . " 
        ORDER BY 
            domain.id DESC";
        $query = $this->db->query($sql);
        return $query->row_array();
    }

    public function get_strand_subjectId($domain_id, $section_id, $subject_group_id, $subject_id ,$session_id=NULL)
    {
        $session_id=IsNullOrEmptyString($session_id) ? $this->current_session :$session_id;
        $sql = "SELECT strand.* FROM strand WHERE domain_id = ".$this->db->escape($domain_id)." ORDER BY strand.id DESC;";
        $query = $this->db->query($sql);
        return $query->row_array();
    }
    public function getdomain($subject_group_subjectid, $subject_group_class_sections_id, $session)
    {
        return $this->db->select('*')->from('domain')->where('domain.subject_group_subject_id', $subject_group_subjectid)->where("session_id", $session)->where('subject_group_class_sections_id', $subject_group_class_sections_id)->get()->result_array();
    }

    public function deletedomain($id, $session)
    {
        $this->db->where("id", $id)->where("session_id", $session)->delete('domain');
    }

    public function deletedomainbulk($id, $session, $subject_group_subject_id)
    {
        $this->db->where("subject_group_class_sections_id", $id)->where("subject_group_subject_id", $subject_group_subject_id)->where("session_id", $session)->delete('domain');
    }

    public function get_subjectstatus($id, $subject_group_class_section_id)
    {
        $sql = "SELECT COUNT(CASE WHEN strand.status = 0 then 1 ELSE NULL END) as 'incomplete', COUNT(CASE WHEN strand.status = 1 then 1 ELSE NULL END) as 'complete',count('*') as total FROM `domain` inner join strand on domain.id=strand.domain_id WHERE domain.subject_group_class_section_id=" . $this->db->escape($subject_group_class_section_id) . "and domain.subject_group_subject_id=" . $this->db->escape($id);
        $query = $this->db->query($sql);
        return $query->result();
    }

    public function get_subject_syllabus($subject_group_subject_id, $subject_id, $time_from, $time_to, $new_date, $staddID, $session)
    {
        $this->db->select('subject_syllabus.*,strand.name as tname')
            ->join("strand", "strand.id = subject_syllabus.strand_id")
            ->from('subject_syllabus')
            ->where("subject_syllabus.subject_group_subject_id", $subject_group_subject_id)
            ->where("subject_syllabus.subject_id", $subject_id)
            ->where("subject_syllabus.time_from", $time_from)
            ->where("subject_syllabus.time_to", $time_to)
            ->where("subject_syllabus.date", $new_date)
            ->where("subject_syllabus.created_by", $staddID)
            ->where("subject_syllabus.session_id", $session);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function ifclassteacher($class_id, $section_id, $staff_id, $subject_group_id, $subject_group_subject_id)
    {
        $class_teacher = $this->db->select('*')->from('class_teacher')->where('class_id', $class_id)->where('section_id', $section_id)->where('staff_id', $staff_id)->get()->num_rows();
        if ($class_teacher > 0) {
            return 1;
        } else {
            $subject_teacher = $this->db->select('*')->from('subject_timetable')->where('class_id', $class_id)->where('section_id', $section_id)->where('staff_id', $staff_id)->where('subject_group_id', $subject_group_id)->where('subject_group_subject_id', $subject_group_subject_id)->get()->num_rows();

            if ($subject_teacher > 0) {
                return 1;
            } else {
                return 0;
            }
        }
    }

    public function getstrandlist($session)
    {
        $class_section_array = $this->customlib->get_myClassSection();
        $this->datatables
            ->select('strand.*,subject_groups.name as sgname,subjects.name as subname,subjects.code as subjects_code,sections.section as sname,sections.id as sectionid,subject_groups.id as subjectgroupsid,subjects.id as subjectid,class_sections.id as csectionid,classes.class as cname,classes.id as classid,domain.name as domainname,domain.subject_group_class_sections_id,domain.subject_group_subject_id')           
            ->searchable('classes.class,sections.section,subjects.name,subject_groups.name,domain.name,strand.name')
            ->orderable('classes.class,sections.section,subjects.name,subject_groups.name,domain.name,strand.name')           
            ->join("domain", "domain.id = strand.domain_id")
            ->join("subject_group_subjects", "subject_group_subjects.id = domain.subject_group_subject_id")
            ->join("subject_groups", "subject_groups.id = subject_group_subjects.subject_group_id")
            ->join("subjects", "subjects.id = subject_group_subjects.subject_id")
            ->join("subject_group_class_sections", "subject_group_class_sections.id = domain.subject_group_class_sections_id", 'inner')
            ->join("class_sections", "class_sections.id = subject_group_class_sections.class_section_id")
            ->join("sections", "sections.id = class_sections.section_id")
            ->join("classes", "classes.id = class_sections.class_id")
            ->where('strand.session_id', $session);
        if (!empty($class_section_array)) {
            $this->datatables->group_start();
            foreach ($class_section_array as $class_sectionkey => $class_sectionvalue) {
                $query_string = "";
                foreach ($class_sectionvalue as $class_sectionvaluekey => $class_sectionvaluevalue) {
                    $query_string = "( class_sections.class_id=" . $class_sectionkey . " and class_sections.section_id=" . $class_sectionvaluevalue . " )";
                    $this->datatables->or_where($query_string);
                }
            }
            $this->datatables->group_end();
        }
        $this->datatables->group_by("domain.subject_group_subject_id");
        $this->datatables->group_by("strand.domain_id");
        $this->datatables->from('strand');
        return $this->datatables->generate('json');

    }
    //-------------------------------------------------------------------------------------------------
    //get stander list from database
    //-------------------------------------------------------------------------------------------------
    public function getoutcomelist($session)
    {
        $class_section_array = $this->customlib->get_myClassSection();
        $this->datatables
            ->select('domain_outcome.*,domain_stander.stander as standername,strand.name as strandname,subject_groups.name as sgname,subjects.name as subname,subjects.code as subjects_code,sections.section as sname,sections.id as sectionid,subject_groups.id as subjectgroupsid,subjects.id as subjectid,class_sections.id as csectionid,classes.class as cname,classes.id as classid,domain.name as domainname,domain.subject_group_class_sections_id,domain.subject_group_subject_id')
            ->searchable('classes.class,sections.section,subjects.name,subject_groups.name,domain.name,strand.name,domain_stander.stander,domain_outcome.outcome')
            ->orderable('classes.class,sections.section,subjects.name,subject_groups.name,domain.name,strand.name,domain_stander.stander,domain_outcome.outcome')
            ->join("domain", "domain.id = domain_outcome.domain_id")
            ->join("subject_group_subjects", "subject_group_subjects.id = domain.subject_group_subject_id")
            ->join("subject_groups", "subject_groups.id = subject_group_subjects.subject_group_id")
            ->join("subjects", "subjects.id = subject_group_subjects.subject_id")
            ->join("subject_group_class_sections", "subject_group_class_sections.id = domain.subject_group_class_sections_id", 'inner')
            ->join("class_sections", "class_sections.id = subject_group_class_sections.class_section_id")
            ->join("sections", "sections.id = class_sections.section_id")
            ->join("classes", "classes.id = class_sections.class_id")
            ->join('strand', 'strand.id = domain_outcome.strand_id')
            ->join('domain_stander', 'domain_stander.id = domain_outcome.stander_id')
            ->where('domain_outcome.session_id', $session);
        if (!empty($class_section_array)) {
            $this->datatables->group_start();
            foreach ($class_section_array as $class_sectionkey => $class_sectionvalue) {
                $query_string = "";
                foreach ($class_sectionvalue as $class_sectionvaluekey => $class_sectionvaluevalue) {
                    $query_string = "( class_sections.class_id=" . $class_sectionkey . " and class_sections.section_id=" . $class_sectionvaluevalue . " )";
                    $this->datatables->or_where($query_string);
                }
            }
            $this->datatables->group_end();
        }
        $this->datatables->group_by("domain.subject_group_subject_id");
        $this->datatables->group_by("domain_outcome.domain_id");
        $this->datatables->from('domain_outcome');
        return $this->datatables->generate('json');

    }
    //-------------------------------------------------------------------------------------------------
    //get outcome list from database
    //-------------------------------------------------------------------------------------------------
    public function getstanderlist($session)
    {
        $class_section_array = $this->customlib->get_myClassSection();
        $this->datatables
            ->select('domain_stander.*,strand.name as strandname,subject_groups.name as sgname,subjects.name as subname,subjects.code as subjects_code,sections.section as sname,sections.id as sectionid,subject_groups.id as subjectgroupsid,subjects.id as subjectid,class_sections.id as csectionid,classes.class as cname,classes.id as classid,domain.name as domainname,domain.subject_group_class_sections_id,domain.subject_group_subject_id')
            ->searchable('classes.class,sections.section,subjects.name,subject_groups.name,domain.name,strand.name,domain_stander.stander')
            ->orderable('classes.class,sections.section,subjects.name,subject_groups.name,domain.name,strand.name,domain_stander.stander')
            ->join("domain", "domain.id = domain_stander.domain_id")
            ->join("subject_group_subjects", "subject_group_subjects.id = domain.subject_group_subject_id")
            ->join("subject_groups", "subject_groups.id = subject_group_subjects.subject_group_id")
            ->join("subjects", "subjects.id = subject_group_subjects.subject_id")
            ->join("subject_group_class_sections", "subject_group_class_sections.id = domain.subject_group_class_sections_id", 'inner')
            ->join("class_sections", "class_sections.id = subject_group_class_sections.class_section_id")
            ->join("sections", "sections.id = class_sections.section_id")
            ->join("classes", "classes.id = class_sections.class_id")
            ->join('strand', 'strand.id = domain_stander.strand_id')
            ->where('domain_stander.session_id', $session);
        if (!empty($class_section_array)) {
            $this->datatables->group_start();
            foreach ($class_section_array as $class_sectionkey => $class_sectionvalue) {
                $query_string = "";
                foreach ($class_sectionvalue as $class_sectionvaluekey => $class_sectionvaluevalue) {
                    $query_string = "( class_sections.class_id=" . $class_sectionkey . " and class_sections.section_id=" . $class_sectionvaluevalue . " )";
                    $this->datatables->or_where($query_string);
                }
            }
            $this->datatables->group_end();
        }
        $this->datatables->group_by("domain.subject_group_subject_id");
        $this->datatables->group_by("domain_stander.domain_id");
        $this->datatables->from('domain_stander');
        return $this->datatables->generate('json');

    }
    public function getdomainlist($session, $id = null)
    {
        $class_section_array = $this->customlib->get_myClassSection();
        $this->datatables
            ->select('domain.*,subject_groups.name as sgname,subjects.name as subname,subjects.code as subjects_code,sections.section as sname,sections.id as sectionid,subject_groups.id as subjectgroupsid,subjects.id as subjectid,class_sections.id as csectionid,classes.class as cname,classes.id as classid')
            ->searchable('classes.class,sections.section,subject_groups.name,subjects.name,domain.name')
            ->orderable('classes.class,sections.section,subject_groups.name,subjects.name,domain.name')
            ->join("subject_group_subjects", "subject_group_subjects.id = domain.subject_group_subject_id")
            ->join("subject_groups", "subject_groups.id = subject_group_subjects.subject_group_id")
            ->join("subjects", "subjects.id = subject_group_subjects.subject_id")
            ->join("subject_group_class_sections", "subject_group_class_sections.id = domain.subject_group_class_sections_id", 'inner')
            ->join("class_sections", "class_sections.id = subject_group_class_sections.class_section_id")
            ->join("sections", "sections.id = class_sections.section_id")
            ->join("classes", "classes.id = class_sections.class_id")
            ->where('domain.session_id', $session);
        if (!empty($class_section_array)) {
            $this->datatables->group_start();
            foreach ($class_section_array as $class_sectionkey => $class_sectionvalue) {
                $query_string = "";
                foreach ($class_sectionvalue as $class_sectionvaluekey => $class_sectionvaluevalue) {
                    $query_string = "( class_sections.class_id=" . $class_sectionkey . " and class_sections.section_id=" . $class_sectionvaluevalue . " )";
                    $this->datatables->or_where($query_string);
                }
            }
            $this->datatables->group_end();
        }
        $this->datatables->group_by("domain.subject_group_subject_id");
        $this->datatables->group_by("domain.subject_group_class_sections_id");
        $this->datatables->from('domain');
        return $this->datatables->generate('json');
    }

    public function getAllStanders($subject_id)
    {
        $this->db->select('id, stander');
        $this->db->from('domain_stander');  // Assuming 'standers' is your table name
        $this->db->where('subject_id', $subject_id);  // Filter by subject
        $this->db->order_by('stander', 'ASC');  // Optional: Order by name

        $query = $this->db->get();
        return $query->result_array();  // Return the result as an array
    }

    public function getOutcomesByStander($stander_id)
    {
        $this->db->select('id, outcome');  // Assuming 'name' is the outcome name field
        $this->db->from('domain_outcome');  // Assuming 'outcomes' is your table name
        $this->db->where('stander_id', $stander_id);  // Filter by stander
        $this->db->order_by('outcome', 'ASC');  // Optional: Order by name

        $query = $this->db->get();
        return $query->result_array();  // Return the result as an array
    }

    public function getSubjectByClass($class_id)
    {
        $this->db->select('*');
        $this->db->from('subjects');
        $this->db->where('class_id',$class_id);
        $query = $this->db->get();
        return $query->result_array();
    }
}

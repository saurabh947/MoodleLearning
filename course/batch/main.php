<?php
	 
	class batch
	{
		
		public function getallsubject()
		{
			global $DB;
			$result=$DB->get_records('vrsspl_subjectmaster');
			return $result;
		}
		
		
		public function getallcoursesubject($id)
		{
			global $DB;
			//echo "ramesh";
			//echo "course id". $id . "\n";
			$category = $DB->get_record('course_categories', array('id' => $id));
			//echo "Categoryid" . $category->parent;
			$result=$DB->get_records('vrsspl_course_subject_info',array('categoryid' => $category->parent));
			
			//print_object($result);
			$result1=$DB->get_records_sql("SELECT s.id,subjectname, c.id as subjectid FROM mdl_vrsspl_subjectmaster as s left outer join mdl_vrsspl_course_subject_info as c on s.id=c.subjectid order by c.id desc");
			//print_object($result1);

			return $result1;
		}
		
		
		public function addnewbatch($record)
		{
			global $DB;
			$DB->insert_record('vrsspl_batch_master', $record);
		}
	}
	
	
?>
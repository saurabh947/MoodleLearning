<?php
	 
	class batch
	{
		
		public function getallsubject()
		{
			global $DB;
			$result=$DB->get_records('vrsspl_subjectmaster');
			return $result;
		}
	}
	
	class courseSubjectInfo()
	{
		public function insertCourseSubject()
		{
			
		}
	}
?>
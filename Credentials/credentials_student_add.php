<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

@session_start() ;

if (isActionAccessible($guid, $connection2, "/modules/Credentials/credentials_student_add.php")==FALSE) {
	//Acess denied
	print "<div class='error'>" ;
		print __($guid, "You do not have access to this action.") ;
	print "</div>" ;
}
else {
	$gibbonPersonID=$_GET["gibbonPersonID"] ;
	$search=NULL ;
	if (isset($_GET["search"])) {
		$search=$_GET["search"] ;
	}
	$allStudents="" ;
	if (isset($_GET["allStudents"])) {
		$allStudents=$_GET["allStudents"] ;
	}
	
	if ($gibbonPersonID=="") {
		print "<div class='error'>" ;
			print __($guid, "You have not specified one or more required parameters.") ;
		print "</div>" ;
	}
	else {
		try {
			if ($allStudents!="on") {
				$data=array("gibbonSchoolYearID"=>$_SESSION[$guid]["gibbonSchoolYearID"], "gibbonPersonID"=>$gibbonPersonID); 
				$sql="SELECT * FROM gibbonPerson JOIN gibbonStudentEnrolment ON (gibbonPerson.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) WHERE gibbonSchoolYearID=:gibbonSchoolYearID AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND gibbonPerson.gibbonPersonID=:gibbonPersonID" ;
			}
			else {
				$data=array("gibbonPersonID"=>$gibbonPersonID); 
				$sql="SELECT DISTINCT gibbonPerson.* FROM gibbonPerson LEFT JOIN gibbonStudentEnrolment ON (gibbonPerson.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) WHERE gibbonPerson.gibbonPersonID=:gibbonPersonID" ;
			}
			$result=$connection2->prepare($sql);
			$result->execute($data);
		}
		catch(PDOException $e) { 
			print "<div class='error'>" . $e->getMessage() . "</div>" ; 
		}
		if ($result->rowCount()!=1) {
			print "<div class='error'>" ;
			print __($guid, "The selected record does not exist, or you do not have access to it.") ;
			print "</div>" ;
		}
		else {
			$row=$result->fetch() ;
			
			//Proceed!
			print "<div class='trail'>" ;
			print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>" . __($guid, "Home") . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . __($guid, getModuleName($_GET["q"])) . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/credentials.php'>" . __($guid, 'Manage Credentials') . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/credentials_student.php&gibbonPersonID=$gibbonPersonID&search=$search&allStudents=$allStudents'>" . formatName("", $row["preferredName"], $row["surname"], "Student") . "</a> > </div><div class='trailEnd'>" . __($guid, 'Add Credential') . "</div>" ;
			print "</div>" ;
			
			if (isset($_GET["addReturn"])) { $addReturn=$_GET["addReturn"] ; } else { $addReturn="" ; }
			$addReturnMessage="" ;
			$class="error" ;
			if (!($addReturn=="")) {
				if ($addReturn=="fail0") {
					$addReturnMessage=__($guid, "Your request failed because you do not have access to this action.") ;	
				}
				else if ($addReturn=="fail2") {
					$addReturnMessage=__($guid, "Your request failed due to a database error.") ;	
				}
				else if ($addReturn=="fail3") {
					$addReturnMessage=__($guid, "Your request failed because your inputs were invalid.") ;	
				}
				else if ($addReturn=="fail4") {
					$addReturnMessage=__($guid, "Your request failed because your inputs were invalid.") ;	
				}
				else if ($addReturn=="success0") {
					$addReturnMessage=__($guid, "Your request was completed successfully. You can now add another record if you wish.") ;	
					$class="success" ;
				}
				print "<div class='$class'>" ;
					print $addReturnMessage;
				print "</div>" ;
			} 
			

			if ($_GET["search"]!="") {
				print "<div class='linkTop'>" ;
					print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/Credentials/credentials_student.php&gibbonPersonID=$gibbonPersonID&search=$search&allStudents=$allStudents'>" . __($guid, 'Back') . "</a>" ;
				print "</div>" ;
			}

			?>
			<form method="post" action="<?php print $_SESSION[$guid]["absoluteURL"] . "/modules/" . $_SESSION[$guid]["module"] . "/credentials_student_addProcess.php?gibbonPersonID=$gibbonPersonID&search=$search&allStudents=$allStudents" ?>">
				<table class='smallIntBorder' cellspacing='0' style="width: 100%">	
					<tr>
						<td style='width: 275px'> 
							<b><?php print __($guid, 'Site Title') ?> *</b><br/>
							<span style="font-size: 90%"><i></i></span>
						</td>
						<td class="right">
							<input name="title" id="title" maxlength=100 value="" type="text" style="width: 300px">
							<script type="text/javascript">
								var title=new LiveValidation('title');
								title.add(Validate.Presence);
							 </script>
						</td>
					</tr>
					<tr>
						<td> 
							<b><?php print __($guid, 'URL') ?></b><br/>
						</td>
						<td class="right">
							<input name="url" id="url" maxlength=255 value="" type="text" style="width: 300px">
							<script type="text/javascript">
								var url=new LiveValidation('url');
								url.add( Validate.Format, { pattern: /(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/, failureMessage: "Must start with http:// or https://" } );
							</script>	
						</td>
					</tr>
					<tr>
						<td> 
							<b><?php print __($guid, 'Username') ?></b><br/>
						</td>
						<td class="right">
							<input name="username" id="username" maxlength=50 value="" type="text" style="width: 300px">
						</td>
					</tr>
					<tr>
						<td> 
							<b><?php print __($guid, 'Password') ?></b><br/>
							<span style="font-size: 90%"><i><?php print __($guid, 'Saved using encryption, but displayed in plain text in the system.') ?></i></span>
						</td>
						<td class="right">
							<input name="password" id="password" maxlength=50 value="" type="text" style="width: 300px">
						</td>
					</tr>
					<tr>
						<td>
							<span style="font-size: 90%"><i>* <?php print __($guid, "denotes a required field") ; ?></i></span>
						</td>
						<td class="right">
							<input type="hidden" name="address" value="<?php print $_SESSION[$guid]["address"] ?>">
							<input type="submit" value="<?php print __($guid, "Submit") ; ?>">
						</td>
					</tr>
				</table>
			</form>
			<?php
		}
	}
}
?>
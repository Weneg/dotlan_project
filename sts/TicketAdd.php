<?php

$MODUL_NAME = "sts";
include_once("../../../global.php");
include("../functions.php");
global $global;

$PAGE->sitetitle = $PAGE->htmltitle = _("Support-Ticket-System");

$queue 		= $_POST['queue'];
$user 		= $_POST['user'];
$agent 		= $_POST['agent'];
$titel 		= $_POST['titel'];
$text 		= nl2br($_POST['user_eingabe']."\n"."\n".$_POST['signatur']);
$status 	= $_POST['status'];
$prio 		= $_POST['prio'];



if($_GET['action'] == "add")
{
//				an, von, Betreff, Betreff Zusatz, Nachricht, Ticket_ID
	user_mail($user,$user_id,$titel,"Neues Support Ticket: ",$text,$ticketid);
	user_pm($user,$user_id,$titel,"Neues Support Ticket: ",$text,$ticketid);


		if(isset($_POST['agent']))
		{
			$sperre = "2";
		}
		else
		{
			$sperre = "1";
		}
	$insert=$DB->query	("
							INSERT INTO
								`project_ticket_ticket`
									(
										id,
										erstellt,
										user,
										titel,
										status,
										prio,
										sperre,
										agent,
										queue,
										text
									)
							VALUES
								(
									NULL,
									'".$datum."',
									'".$user."',
									'".$titel."',
									'".$status."',
									'".$prio."',
									'".$sperre."',
									'".$agent."',
									'".$queue."',
									'".$text."'
								);"
						);

	$output .= "<meta http-equiv='refresh' content='0; URL=index.php'>";
	$out_ticket_show_name =
							$DB->fetch_array(
												$DB->query(	"
																SELECT
																	*
																FROM
																	user
																WHERE
																	id='".$user."'
															")
											);
	$PAGE->redirect("{BASEDIR}admin/projekt/sts/",$PAGE->sitetitle,"Das Ticket ".$titel." f&uuml;r ".$out_ticket_show_name['vorname']." '".$out_ticket_show_name['nick']."' ".$out_ticket_show_name['nachname']." wurde erstellt <br>Nachricht: <br> ".$text.".");
}

/*###########################################################################################
Admin PAGE
*/

if(!$DARF["add"]) $PAGE->error_die($HTML->gettemplate("error_nopermission"));

else
{
include("header.php");
include("news.php");
$output .=
"

<form name='antwort' method='post' action='?action=add' onSubmit='return checkSubmit()'>
	<table width='100%' border='0'>
		<tbody>
			<tr>
				<td width='10%' class='contentkey'>Von:</td>
                <td width='60%' class='contentvalue'>
					<select name='queue'>
					";

						$sql_list_queue = $DB->query("SELECT * FROM project_ticket_queue");
						while($out_list_queue = $DB->fetch_array($sql_list_queue))
					{// begin while

									$output .= "

									<option value='".$out_list_queue['id']."'>".$out_list_queue['name']."</option>
									";
					}

						$output .= "
									</select>
                            </td>
                        </tr>
                        <tr>
                            <td class='contentkey'>An:</td>
                            <td class='contentvalue'>
							<div id='divsearch' style=' display:none;'>
								<table cellspacing='0' cellpadding='0'  border='0'>
									<tr>
										<td>
											<input type='text' id='insearch' name='search' size='60' >
										</td>
										<td>&nbsp;</td>
										<td>
											<input type='button' value='Suchen' onClick='javascript:searchUser();'>
										</td>
									</tr>
								</table>
							</div>
							<div id='divselect' style=' display:none;'>
								<table cellspacing='0' cellpadding='0' border='0'>
									<tr>
										<td >
											<select id='inselect' name='user'  ></select>
										</td>
										<td>&nbsp;</td>
										<td>
											<input type='button' value='X' onClick='javascript:clearSearch();'>
										</td>
									</tr>
								</table>
							</div>
							<noscript>
							<b>Javascript is needed for UserSearch</b>
							</noscript>

						<iframe frameborder='0' style='width:0px; height:0px;' src='about:blank' id='operasucks'></iframe>
						<script type='text/javascript' src='/user/xmlusersearch.js'></script>
						<script type='text/javascript'>
						<!--

						// define variables needed by xmlusersearch.js
						var inselect	= document.getElementById('inselect');
						var divselect	= document.getElementById('divselect');
						var insearch	= document.getElementById('insearch');
						var divsearch	= document.getElementById('divsearch');
						var xmllink	= '/user/?do=xmlsearch';

						function checkSubmit()
						{
							if(insearch.value != '') {
								searchUser();
								return false;
							}
							if(inselect.length == 0 || inselect.options[0].value == '' || inselect.options[0].value == '0') {
								alert('Es wurde kein Benutzer gewaehlt');
								return false;
							}
						}

						initUserSearch('0');

						//-->
						</script>


						<br />
					</td>
                        </tr>
						<!--start OwnerSelection-->
                        <tr>
                            <td class='contentkey'>Besitzer:</td>
                            <td class='contentvalue'>
								<select name='agent'>
								<option value='' selected='selected'>-</option>
						";

				$sql_ticket_show_orga = $DB->query("SELECT * FROM user_orga ");
					while($out_ticket_show_orga = $DB->fetch_array($sql_ticket_show_orga))
					{
						$out_ticket_show_orga_name =
								$DB->fetch_array(
													$DB->query(	"
																	SELECT
																		*
																	FROM
																		user
																	WHERE
																		id='".$out_ticket_show_orga['user_id']."'
																")
												);
						if($out_ticket_show_orga['user_id'] == $out_ticket['agent'] )
						{

							$output .="	<option value='".$out_ticket_show_orga['user_id']."' selected>".$out_ticket_show_orga_name['vorname']." '".$out_ticket_show_orga_name['nick']."' ".$out_ticket_show_orga_name['nachname']."</option>
									";
						}
						else
						{
							$output .="	<option value='".$out_ticket_show_orga['user_id']."'>".$out_ticket_show_orga_name['vorname']." '".$out_ticket_show_orga_name['nick']."' ".$out_ticket_show_orga_name['nachname']."</option>
									";
						}
					}

						$output .="</select>
                            </td>
                        </tr>
                        <tr>
                            <td class='contentkey'>Betreff:</td>
                            <td class='contentvalue'>
                                <input type='text' size='60' name='titel'>
                            </td>
                        </tr>
                        <tr>
                            <td class='contentkey'>Text:</td>
                            <td class='contentvalue'>
									<textarea wrap='hard'name='user_eingabe'  rows='15' cols='60' style='background: none repeat scroll 0 0 buttonface;'></textarea>
                            </td>
                        </tr>
<!--					<tr>
                            <td class='contentkey'>Signatur:</td>
                            <td class='contentvalue'>
                                <textarea name='signatur' rows='8' cols='60' wrap='hard' readonly='readonly'>Freundliche Gr&uuml;&szlig;e / Best regards

".$out_signatur_user['vorname']." ".$out_signatur_user['nachname']."
".$out_signatur['funktion']."

".$global['sitename']."
</textarea>
                            </td>
                        </tr>
-->
                        <tr>
                            <td class='contentkey'>Status des Tickets:</td>
                            <td class='contentvalue'>
								<select name='status'>
";

						$sql_list_status = $DB->query("SELECT * FROM project_ticket_status");
						while($out_list_status = $DB->fetch_array($sql_list_status))
					{// begin while
									if($out_list_status['id'] == 3)
									{
									$output .= "

									<option value='".$out_list_status['id']."' selected>".$out_list_status['name']."</option>
									";
									}
									else
									{
									$output .= "

									<option value='".$out_list_status['id']."'>".$out_list_status['name']."</option>
									";
									}
					}

						$output .= "
								</select>
							</td>
                        </tr>
                        <tr>
                            <td class='contentkey'>Priorit&auml;t:</td>
                            <td class='contentvalue'>
									<select name='prio'>
						";

						$sql_list_prio = $DB->query("SELECT * FROM project_ticket_prio");
						while($out_list_prio = $DB->fetch_array($sql_list_prio))
					{// begin while

									if($out_list_prio['id'] == 3)
									{
									$output .= "

									<option value='".$out_list_prio['id']."' selected>".$out_list_prio['name']."</option>
									";
									}
									else
									{
									$output .= "

									<option value='".$out_list_prio['id']."'>".$out_list_prio['name']."</option>
									";
									}
					}

						$output .= "
									</select>
							</td>
                        </tr>
                    </tbody>
				</table>

				<input type='submit' value='Ticket anlegen' accesskey='s' >
</form>

";


}
// ENDE darf Sehen

$PAGE->render(utf8_decode(utf8_encode($output) ));
?>

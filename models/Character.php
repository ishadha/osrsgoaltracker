<?php
class Character {
    public function addNew($db, $data) {
        $RSN = str_replace(' ','_',$data[0]['rsn']);
        
        $characterCheck = $db->prepare("select * from characters where rsn = '{$RSN}' and active = 'Y'");
        $characterCheck->execute();
        
        if ($characterCheck->rowCount() <= 0) {
            $osrsAPI ="/m=hiscore_oldschool/index_lite.ws?player=";
            $hsLink = $osrsAPI . $RSN;
//            $sql= "insert into characters(rsn, userID, characterType, highScoreLink, active) VALUES ('{$RSN}', {$data[0]['userID']}, '{$data[0]['accountType']}','{$hsLink}', 'Y')";
            $characterAdd = $db->prepare("insert into characters(rsn, userID, characterType, highScoreLink, active) VALUES ('{$RSN}', {$data[0]['userID']}, '{$data[0]['accountType']}','{$hsLink}', 'Y')");
            
            if($characterAdd->execute()){     
                return json_encode(array("status" => "success"));
        } else {
            return json_encode(array("status" => "fail", "message" => "there was an error", "sql" => $sql));
        }
                
        } else {
               return json_encode(array("status" => "fail", "message" => "rsn already exist"));
        }
    }
    public function getAll($db, $id) {
        try{
            $getAll = $db->prepare("select * from characters where id = {$id}");
            $getAll->execute();


            if($getAll->rowCount() > 0){
               while($row = $getAll->fetch())
               {
                   $character[] = array("rsn" => $row['rsn'], "link" => $row['highScoreLink'], "characterType" => $row['characterType']);
               }
            }
            return json_encode(array("status" => "success", "character" => $character));
        } catch (PDOException $e) { // The authorization query failed verification
             return json_encode(array("status" => "failed",
                                                            "error" => "Catch Exception: " . $e->getMessage()
             ));
        }
    }
    function getStats($link) {
		// Create an array of skills
		$skills = array('Overall', 'Attack', 'Defence', 'Strength', 'Hitpoints', 'Ranged', 'Prayer', 'Magic', 'Cooking', 'Woodcutting', 'Fletching', 'Fishing', 'Firemaking', 'Crafting', 'Smithing', 'Mining', 'Herblore', 'Agility', 'Thieving', 'Slayer', 'Farming', 'Runecraft', 'Hunter', 'Construction', 'Summoning', 'Duel Tournament', 'Bounty Hunters', 'Bounty Hunter Rogues', 'Fist of Guthix');

		$hs = @file_get_contents('http://services.runescape.com' . $link);
		$out = Array();

		if (! $hs)
			return null;
		if (strpos($hs, '404 - Page not found'))
			return null;

		$stats = explode("\n", $hs);

		// Loop through the skills
		for($i = 0; $i<count($skills);$i++) {
			// Explode each skill into 3 values - rank, level, exp
			$stat = explode(',', $stats[$i]);
			$out[$skills[$i]] = Array();
			$out[$skills[$i]]['rank'] = $stat[0];
			$out[$skills[$i]]['level'] = $stat[1];
			$out[$skills[$i]]['xp'] = $stat[2];
		}
	return $out;
	}
}
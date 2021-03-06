<?php
    // получить текущих игроков раунда
    function getRoundPlayers($roundId)
    {
        $link = getDBConnection();
        $data = array();
        if (mysqli_select_db($link, getDBName()))
        {
            $roundId = intval($roundId);
            $query = mysqli_query($link, "SELECT user FROM roundActivity WHERE round = $roundId AND state = 'ACT'");
            
            while ($row = mysqli_fetch_assoc($query))
                $data[] = getCurrentStrategy($row['user'], mysqli_result(mysqli_query($link, "SELECT tournament FROM rounds WHERE id = $roundId"), 0));
            mysqli_free_result($query);
        }
        return $data;
    }
    
    // получить информацию о раунде
    function getRoundData($roundId)
    {
        $link = getDBConnection();
        $data = array();
        if (mysqli_select_db($link, getDBName()))
        {
            $roundId = intval($roundId);
            $query = mysqli_query($link, "SELECT * FROM rounds WHERE id = $roundId");
            
            if ($row = mysqli_fetch_assoc($query))
            {
                $data['id']         = $row['game'];
                $data['tournament'] = $row['tournament'];
                $data['game']       = $row['game'];
                $data['name']       = $row['name'];
                $data['date']       = $row['date'];
                $data['visible']    = $row['visible'];
            }
            if (!$row || (!isAdmin() && !$data['visible']))
            {
                $data['id']         = -1;
                $data['tournament'] = "Unknown";
                $data['game']       = "Unknown";
                $data['name']       = "Unknown";
                $data['date']       = "Unknown";
                $data['visible']    = false;
            }
            
            mysqli_free_result($query);
            
            $query = mysqli_query($link, "SELECT * FROM games WHERE id = {$data['id']}");
            if ($row = mysqli_fetch_assoc($query))
                $data['gameName'] = $row['name'];
            else
                $data['gameName'] = "Unknown";
            
            mysqli_free_result($query);
        }
        return $data;
    }

    
    // проверка завершения проверек раунда
    function checkRoundEnding($roundId)
    {   
        if ($roundId == -1)
            return false;
        $roundCount = getCheckedDuels($roundId);
        if ($roundCount == 0)
            return false;
        else
            return $roundCount == countDuelsInRound($roundId);
    }

    // Если в дуэлях раунд проведен, то повторный раз можно не делать
    function countDuelsInRound($roundId)
    {
        $link = getDBConnection();
        if (mysqli_select_db($link, getDBName()))
        {
            $roundId = intval($roundId);
            return mysqli_result(mysqli_query($link, "SELECT COUNT(*) FROM duels WHERE round = $roundId"), 0);
        }
    }
    
    function isRoundVisible($roundId)
    {
        $link = getDBConnection();
        if (mysqli_select_db($link, getDBName()))
        {
            $roundId = intval($roundId);
            return mysqli_result(mysqli_query($link, "SELECT COUNT(*) FROM rounds WHERE id = $roundId AND visible = true"), 0);
        }
    }
    
    function setRoundVisible($roundId, $visibility = true)
    {
        if ($visibility)
            $visibility = "true";
        else
            $visibility = "false";
        $link = getDBConnection();
        if (mysqli_select_db($link, getDBName()))
        {
            $roundId = intval($roundId);
            if (mysqli_query($link, "UPDATE rounds SET visible = $visibility WHERE id = $roundId"))
                return 0;
            else
                return 1;
        }
        else return 2;
    }
    
    // Количество проверенных дуэлей
    function getCheckedDuels($roundId)
    {
        $link = getDBConnection();
        if (mysqli_select_db($link, getDBName()))
        {
            $roundId = intval($roundId);
            return mysqli_result(mysqli_query($link, "SELECT COUNT(*) FROM duels WHERE round = $roundId AND status <> 'W'"), 0);
        }
    }
    
    // Создать раунд
    function createRoundErr($tournamentId, $roundName, $checker, $previousRound, $seed)
    {
        if (!isAdmin())
        {
            return 4;
        }
        $link = getDBConnection();
        if (mysqli_select_db($link, getDBName()))
        {
            $tournament = getTournamentList($tournamentId);
            $gameId = $tournament['game'];
            $roundName = mysqli_real_escape_string($link, $roundName);
            $checker = mysqli_real_escape_string($link, $checker);
            $previousRound  = mysqli_real_escape_string($link, $previousRound);
            
            if (mysqli_query($link, "INSERT INTO rounds SET tournament = $tournamentId, name = '$roundName', game = $gameId, checker = $checker, previousRound = $previousRound, seed = $seed"))
                return 0;
            else
                return 1;
        }
        else return 2;
    }

    // Создать раунд
    function createRound($tournamentId, $roundName, $checker, $previousRound, $seed)
    {
        if (!isAdmin())
        {
            return -1;
        }
        $link = getDBConnection();
        if (mysqli_select_db($link, getDBName()))
        {
            $tournament = getTournamentList($tournamentId);
            $gameId = $tournament['game'];
            $roundName = mysqli_real_escape_string($link, $roundName);
            $checker = mysqli_real_escape_string($link, $checker);
            $previousRound  = mysqli_real_escape_string($link, $previousRound);
            
            if (mysqli_query($link, "INSERT INTO rounds SET tournament = $tournamentId, "
                ."name = '$roundName', game = $gameId, checker = $checker, "
                ."previousRound = $previousRound, seed = $seed"))
            {
                return mysqli_insert_id($link);
            }
        }
        
        return -1;
    }
    
    // Обновить раунд
    function updateRound($tournamentId, $roundId, $roundName, $checker, $previousRound, $seed)
    {
        if (!isAdmin())
        {
            return 4;
        }
        $link = getDBConnection();
        if (mysqli_select_db($link, getDBName()))
        {
            $tournament = getTournamentList($tournamentId);
            $gameId = $tournament['game'];
            
            $roundName = mysqli_real_escape_string($link, $roundName);
            $checker = intval($checker);
            $previousRound = intval($previousRound);
            
            if (mysqli_query($link, "UPDATE rounds SET tournament = $tournamentId, name = '$roundName', game = $gameId, checker = $checker, previousRound = $previousRound, seed = $seed WHERE id = $roundId"))
                return 0;
            else
                return 1;
        }
        else return 2;
    }
    
    // Получить предыдущий раунд
    function getPreviousRound($roundId)
    {
        $link = getDBConnection();
        $previousRound = -1;
        if (mysqli_select_db($link, getDBName()))
        {
            $roundId = intval($roundId);
            $query = mysqli_query($link, "SELECT previousRound FROM rounds WHERE id = $roundId");
            $data = mysqli_fetch_assoc($query);
            $previousRound = $data['previousRound'];
            mysqli_free_result($query);
        }
        return $previousRound;
    }
    
?>

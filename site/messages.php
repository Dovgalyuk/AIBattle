<?php include_once("procedures.php"); ?>
<?php
    $id = intval($_GET['id']);
    if (isset($_GET['id']))
    {
        $messages = getUserMessages(0, -1, $id);
        if (isset($messages[$id]))
        {
            $msg = $messages[$id];
            if ($msg['recevier'] == getActiveUserID())
                markMessageAsViewed($id);
?>

<?php include("top.php"); ?>
<div class="content container">
            <br />
            <p style="font-size:16px;">
            Тема: <?php echo $msg['title']; ?><br />
            От кого: <?php echo getNicknameById($msg['sender']); ?><br />
            Кому: <?php echo getNicknameById($msg['recevier']); ?><br />
            Сообщение:
            </p>           
            <?php
                echo $msg['text'];
            ?> 

</div>

<?php include("bottom.php"); ?>
<?php
        };
    };
?>

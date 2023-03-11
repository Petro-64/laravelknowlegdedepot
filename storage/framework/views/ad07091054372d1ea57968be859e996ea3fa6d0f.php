<?php $__env->startSection('content'); ?>
    <main role="main" class="container">
        <div class="clickBlocker">
            <img src="<?php echo e(asset('img/loader.gif')); ?>">
        </div>
    <h1 class="mt-3">Testing...</h1>
    <br/>
    <table>
    <tr><td><h4>Subject: <?php echo e($subjectName); ?></h4></td><td>&nbsp;&nbsp;&nbsp;Answered: <?php echo e($answered); ?> questions</td><td>&nbsp;&nbsp;&nbsp;Correct: <?php echo e($correct); ?> questions</td></tr>
    </table>
    <?php if($ifRemainQuestions == 1): ?>
        <div id="timer">
            <div id="seconds">Remaining time: 60 seconds</div>
        </div>
        <p>Just click proper answer</a>
        <p><b>Question:</b> <?php echo e($question); ?></p>
        <form class="hiddenForm">
            <?php echo e(csrf_field()); ?>

            <input type="hidden" class="currentQuestionId" value="<?php echo e($currentQuestionId); ?>">
        </form>
        <?php $__currentLoopData = $answers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $answer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $toShow = htmlspecialchars_decode($answer['name']);
                if(strlen($answer['name']) > 0){
            ?>
                <div class="answerVersion" data-id="<?php echo e($answer['id']); ?>"><p><?php echo e($toShow); ?></p></div>
            <?php             
                } 
            ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <br/><br/><br/><br/><br/>
        <button type="button" class="btn btn-danger" id="stopTestingButton">I'd like to stop testing
        <?php if(auth()->guard()->guest()): ?>
        *
        <?php endif; ?>
        </button>
        <?php if(auth()->guard()->guest()): ?>
            <p>*If you're guest, your testing result will be lost</p>
        <?php endif; ?>
    <?php else: ?>
        <p><b>All <?php echo e($answered); ?> questions we have in database at the moment are answered, thanks</b></p>
        <button type="button" class="btn btn-danger" id="backTo">Back to start testng page
    <?php endif; ?>
    <script>
        function makeTimer(endTime) {
            var now = new Date();
            now = (Date.parse(now) / 1000);
            var timeLeft = endTime - now;
            var hours = Math.floor((timeLeft - (Math.floor(timeLeft / 86400) * 86400)) / 3600);
            var minutes = Math.floor((timeLeft - (Math.floor(timeLeft / 86400) * 86400) - (hours * 3600 )) / 60);
            var seconds = Math.floor((timeLeft - (Math.floor(timeLeft / 86400) * 86400) - (hours * 3600) - (minutes * 60)));
            if (seconds < "10") { seconds = "0" + seconds; }
            $("#seconds").html("Remaining time: " + seconds + "<span> Seconds</span>");	
            if(seconds < 1){
                window.location.href = "/tests";
            }	
        }
        var endTime = new Date();			
        endTime = (Date.parse(endTime) / 1000) + 60;
        setInterval(function() { makeTimer(endTime); }, 1000);
        $( document ).ready(function() {
            $( ".answerVersion" ).click(function() {
                var url = "/corransw/" + $(".currentQuestionId").val();
                var th = $(this);
                var jqxhr = $.get(url)
                .done(function(data) {
                    $(".clickBlocker").css("display", "block");
                    $(".answerVersion").each(function(){
                        var th = $(this);
                        var thisId = th.attr("data-id"); 
                        $.each(data.data , function( index, value ) {
                            console.log(value.id);
                            if(value.id == thisId){
                                if(value.correct == 1){
                                    th.css("background-color", "#8ec78e");
                                } else {
                                    th.css("background-color", "#ffbec1");
                                }
                            }
                        });
                    });
                })
                .fail(function() {
                    alert("Network error, please try again later");
                });
                function reloadd(){
                    window.location.href = "/testing/" + th.attr("data-id");
                }
                setTimeout(reloadd, 1500);
            });

            $( "#stopTestingButton, #backTo").click(function() {
                window.location.href = "/tests";
            });            
        });
    </script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app_no_navigation', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/laravelknowlegdedepot/resources/views/testing.blade.php ENDPATH**/ ?>
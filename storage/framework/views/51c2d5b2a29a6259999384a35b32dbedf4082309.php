

<?php $__env->startSection('content'); ?>
    <!-- Begin page content -->
    <main role="main" class="container">
      <h1 class="mt-3">Edit Questions</h1>
      <div id="questionsEditWrapper">
      <?php if(count($subjects) > 0): ?>
      <label for="subjectsSelect">Select subject you want to edit existing question:</label>
        <?php echo $__env->make('common.subjectsdropdown', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
       
          <?php if(isset($id)): ?>
            <?php if($id != 0): ?>
            Filter questions:
            <select id="activeness">
                <option value="1" 
                <?php if(isset($act)): ?>
                    <?php if($act == "1"): ?>
                    selected
                    <?php endif; ?>
                <?php endif; ?>
                >All</option>
                <option value="2"
                <?php if(isset($act)): ?>
                    <?php if($act == "2"): ?>
                    selected
                    <?php endif; ?>
                <?php endif; ?>
                >Active</option>
                <option value="3"
                <?php if(isset($act)): ?>
                    <?php if($act == "3"): ?>
                    selected
                    <?php endif; ?>
                <?php endif; ?>
                >Unactive</option>
            </select>
            </br></br>
            <table id="questionsEditTable" class="table"><thead><tr><td>Question Id</td><td>Status</br>(click to change)</td><td>Question</td><td>Edit</td></tr></thead>
              
                <?php $__currentLoopData = $questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <?php
                    if($question['active'] == 1){
                      $phraze = "active";
                      $trClassName = "white";
                    } else {
                      $phraze = "unactive";
                      $trClassName = "grey";
                    }
                    $toShow = htmlspecialchars_decode($question['name']);
                  ?>
                  <tr class="<?php echo e($trClassName); ?>"><td class="questionId" data-id="<?php echo e($question['id']); ?>"><?php echo e($question['id']); ?></td><td class="controlActivness" data-active="<?php echo e($question['active']); ?>"><?php echo e($phraze); ?></td>
                  <td class="questionText" data-id="<?php echo e($question['id']); ?>"><?php echo e($toShow); ?></td>
                      <td><button type="button" class="btn btn-primary btn-sm show-answers" data-id="<?php echo e($question['id']); ?>" data-active="<?php echo e($question['active']); ?>">Edit</button></td></tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              <?php endif; ?>
            </table>
            <?php echo e($questions->links()); ?>

          <?php endif; ?>
      <?php endif; ?>
      </div>
      <div id="editForm"><button type="button" class="btn btn-danger btn-sm">Cancel</button><button type="button" class="btn btn-success btn-sm">Save</button><button type="button" class="btn btn-warning btn-sm"></button>
      <div class="questionWrapper"><textarea class="form-control" rows="5"></textarea></div>
      <div class="tabWrapper"></div>
      <div class="errorWrapper"></div>
    </div>
    <form class="hiddenForm">
        <?php echo e(csrf_field()); ?>

        <input type="hidden" id="questionId">
   </form>
    </main>
    <script>
      $( document ).ready(function() {
        $('#activeness').change(function() {
          var value = $(this).val();
            console.log(value);
            var expires = new Date();
            expires.setTime(expires.getTime() + (2 * 24 * 60 * 60 * 1000));
            document.cookie = 'activeness =' + value + ';expires=' + expires.toUTCString();
            var sujId = $( "#subjectsSelect" ).val();
            window.location.href = "/questions_edit/" + sujId + "/" + value;
            ///location.reload();
        });
        
        $( "#subjectsSelect" ).change(function() {
          window.location.href = "/questions_edit/" + $( this ).val() + "/1" ;
        });
        $(".btn-danger").on("click", function(){
          console.log("cancel");
          $("#questionsEditWrapper").css("display", "block");
          $("#editForm").css("display", "none");
        });
        $("td.controlActivness").on("click", function(){
          var active = $(this).attr("data-active");
          active == 1 ? activeValue = 0 : activeValue = 1;
          var questionIdValue = $(this).parent().find("td.questionId").text();
          console.log("questionId = ", questionIdValue);
          var tokenValue = $(".hiddenForm input").val();
          var jqxhr = $.post( "/api/activequestionedit", {_token: tokenValue, active: activeValue, questionId: questionIdValue})
              .done(function(data) {
                location.reload();
              })
              .fail(function() {
                alert("Network error, please try again later");
              });
        })
        $(".show-answers").on("click", function(){
          var id = $(this).attr("data-id");
          var active = $(this).attr("data-active");
          if(active == 1){
            $(".btn-warning").text("Deactivate this question");
          } else {
            $(".btn-warning").text("Activate this question");
          }
          $("#questionId").val(id);
          $(".questionWrapper textarea").val($(this).parent().parent().find(".questionText").text() );
          var requestUrl = "/api/answerstoquestion/" + id;
          var jqxhr = $.get( requestUrl )
          .done(function(data) {
            var row = '<table class="table-bordered"><tr>';
            var radioRow = '<tr>';
            var cellClass = '';
            switch(data.data.length) {
              case 2:
                var extraTd = '<td class="uncorrect"><textarea class="form-control" rows="8"></textarea></td><td class="uncorrect"><textarea class="form-control" rows="8"></textarea></td>';
                var extraTdRadio = '<td class="uncorrect" data-order="2"><input type="radio"  name="correct" value=""></td><td class="uncorrect" data-order="3"><input type="radio"  name="correct" value=""></td>';
                break;
              case 3:
                var extraTd = '<td class="uncorrect"><textarea class="form-control" rows="8"></textarea></td>';
                var extraTdRadio = '<td class="uncorrect" data-order="3"><input type="radio"  name="correct" value=""></td>';
                break;
              case 4:
              var extraTd = '';
              var extraTdRadio = '';
                break;  
              default:
              var extraTd = '';
              var extraTdRadio = '';
            }
            $.each(data.data, function( index, value ) {
              if(value.correct == 1){
                cellClass = ' class="correct"';
                checked = ' checked ';
              } else {
                cellClass = ' class="uncorrect"';
                checked = '';
              }
              row = row + '<td' + cellClass + '><textarea class="form-control" rows="8" data-answerId="' + value.id + '">' + value.text + '</textarea></td>';
              radioRow = radioRow + '<td' + cellClass + ' data-order="' + index + '"><input type="radio"  name="correct" value="' + value.id + '" ' + checked + ' ></td>'
            });
            radioRow = radioRow + extraTdRadio + '</tr>';
            row = row + extraTd + '</tr>' + radioRow + '</table>';
            $(".tabWrapper").html(row);
            $("#questionsEditWrapper").css("display", "none");
            $("#editForm").css("display", "block");
          })
          .fail(function() {
            alert( "Please try again later" );
          })
        })
        $(document).on("click", "table.table-bordered tr:nth-child(2) td" , function() {
            var index = $(this).attr("data-order");
            var order = parseInt(index) + 1;
            var currentAnswer = $("table.table-bordered tr:nth-child(1) td:nth-child(" + order + ") textarea").val();
            if(currentAnswer != ""){
              $("table.table-bordered tr td").removeAttr('class');
              $("table.table-bordered tr td").addClass("uncorrect");
              $(this).removeAttr('class');
              $(this).addClass("correct");
              $("table.table-bordered tr:nth-child(1) td:nth-child(" + order + ")").removeAttr('class');
              $("table.table-bordered tr:nth-child(1) td:nth-child(" + order + ")").addClass("correct");
              $(this).find("input").click(function( event ) {
                event.stopPropagation();
              });
              $(this).find("input").trigger("click");
            }
        });
        $(document).on("click", "table.table-bordered" , function() {
          $(".errorWrapper").text("");
        })

        $(document).on("click", "table.table-bordered input" , function() {
          $(".errorWrapper").text("");
        })

        $(".btn-warning").on("click", function(){
          var questionIdValue = $("#questionId").val();
          console.log("questionId = ", questionIdValue);
          var tokenValue = $(".hiddenForm input").val();
          var jqxhr = $.post( "/api/activequestioneditbyid", {_token: tokenValue, questionId: questionIdValue})
              .done(function(data) {
                location.reload();
              })
              .fail(function() {
                alert("Network error, please try again later");
              });
        })

        $(".btn-success").on("click", function(){
          var tokenValue = $(".hiddenForm input").val();
          var questionValue = $(".questionWrapper textarea").val();
          var questionIdValue = $("#questionId").val();
          jsonObj = [];
          var checked = $("table.table-bordered tr:nth-child(2) td input[name='correct']:checked").val();
          jsonObj["checkedId"] = checked; 
          var num = 4;
          for(i = 1; i<=4; i++){
            item = {};
            var currentAnswer = $("table.table-bordered tr:nth-child(1) td:nth-child(" + i + ") textarea").val();
            var currentId = $("table.table-bordered tr:nth-child(2) td:nth-child(" + i + ") input").val();
            var ifChecked = $("table.table-bordered tr:nth-child(2) td:nth-child(" + i + ") input[name='correct']").is(':checked');
            item["answer"] = currentAnswer;
            item["correct"] = ifChecked;
            item["id"] = currentId;
            jsonObj.push(item);
          }
          var jqxhr = $.post( "/api/answerquestionedit", {_token: tokenValue, question: questionValue, questionId: questionIdValue, answers: jsonObj})
          .done(function(data) {
            if (data.success == "succcess"){
              location.reload();
            } else {
              $(".errorWrapper").text(data.description);
            }
          })
          .fail(function() {
            alert("Network error, please try again later");
          });
        })
      });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /mnt/c/Users/Peter/Code/homestead/resources/views/questions_edit.blade.php ENDPATH**/ ?>
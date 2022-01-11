

<?php $__env->startSection('content'); ?>
    <!-- Begin page content -->
    <main role="main" class="container">
      <h1 class="mt-3">Questions</h1>
      <?php if(count($subjects) > 0): ?>
      <label for="subjectsSelect">Select subject you want to add new question to:</label>
        <?php echo $__env->make('common.subjectsdropdown', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php if(isset($id)): ?>
          <?php if($id != 0): ?>

          <?php if(session('success')): ?>
            <div class="alert alert-success">
                <?php echo session('success'); ?>

                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
            </div>
          <?php endif; ?>
          
          <?php if(session('error')): ?>
            <div class="alert alert-danger">
                <?php echo session('error'); ?>

                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
            </div>
          <?php endif; ?>

          <?php echo $__env->make('common.errors', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
          <form action="/question" method="POST" class="form-horizontal">
            <?php echo e(csrf_field()); ?>

            <div class="form-group">
              <table style="width: 100%; text-align: center">
                <tr><td colspan="2"><label for="task" class="col-sm-3 control-label">New Question</label></td></tr>
                <tr><td colspan="2"><textarea name="question" id="question-name" rows="3" cols="" class="form-control"><?php echo e(old('question')); ?></textarea></td></tr>
                <tr data-click="first"><td colspan="2"><label for="first-radio">Answer 1</label></td></tr>
                <tr data-click="first"><td><textarea name="answer[]" id="answer-name-1" rows="3" cols="" class="form-control"></textarea></td><td style="width: 20%"><input type="radio" id="first-radio" name="correct" value="a0"></td></tr>
                <tr data-click="second"><td colspan="2"><label for="second-radio">Answer 2</label></td></tr>
                <tr data-click="second"><td><textarea name="answer[]" id="answer-name-2" rows="3" cols="" class="form-control"></textarea></td><td style="width: 20%"><input type="radio" id="second-radio" name="correct" value="a1"></td></tr>
                <tr data-click="third"><td colspan="2"><label for="third-radio">Answer 3</label></td></tr>
                <tr data-click="third"><td><textarea name="answer[]" id="answer-name-3" rows="3" cols="" class="form-control"></textarea></td><td style="width: 20%"><input type="radio" id="third-radio" name="correct" value="a2"></td></tr>
                <tr data-click="fourth"><td colspan="2"><label for="fourth-radio">Answer 4</label></td></tr>
                <tr data-click="fourth"><td><textarea name="answer[]" id="answer-name-4" rows="3" cols="" class="form-control"></textarea></td><td style="width: 20%"><input type="radio" id="fourth-radio" name="correct" value="a3"></td></tr>
                <tr><td colspan="2"><input name="subjectId" type="hidden" value="<?php echo e($id); ?>"></td></tr>
                <tr style="height: 140px">
                  <td colspan="2">
                    <button type="submit" id="questionSubmit" class="btn btn-primary" disabled="disabled">
                        <i class="fa fa-plus"></i> Add Question
                    </button>
                  </td>
                </tr>
              </table>
            </div>
          </form>
          <?php endif; ?>
        <?php endif; ?>
      <?php endif; ?>
    </main>
    <script>
      $( document ).ready(function() {
        $( "#subjectsSelect" ).change(function() {
          window.location.href = "/questions/" + $( this ).val();
        });
        $("tr").click(function(){
          var num = $(this).attr("data-click");
          if(num !="undefined"){
              $("#questionSubmit").removeAttr("disabled");
              var name = num + "-radio";
              $('input:radio[id=' + name + ']')[0].checked = true;
          }
        })
      });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /mnt/c/Users/Peter/Code/homestead/resources/views/questions.blade.php ENDPATH**/ ?>
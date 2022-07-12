

<?php $__env->startSection('content'); ?>
    <!-- Begin page content -->
    <main role="main" class="container">
      <h1 class="mt-3">Subjects</h1>
      <!-- Bootstrap Boilerplate... -->

    <div class="panel-body">
        <!-- Display Validation Errors -->
        <?php echo $__env->make('common.errors', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <?php if(session('success')): ?>
            <div class="alert alert-success">
                <?php echo session('success'); ?>

            </div>Mine works, I'm lucky ;-)
        <?php endif; ?>
        <!-- New Task Form -->
        <form action="/subject" method="POST" class="form-horizontal">
            <?php echo e(csrf_field()); ?>


            <!-- Task Name -->
            <div class="form-group">
                <label for="task" class="col-sm-3 control-label">New subject</label>

                <div class="col-sm-6">
                    <input type="text" name="subject_name" id="subject_name" class="form-control" value="<?php echo e(old('subject_name')); ?>">
                </div>
            </div>

            <!-- Add Task Button -->
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-6">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Add Subject
                    </button>
                </div>
            </div>
        </form>
        <!-- Current Tasks -->
        <?php if(count($subjects) > 0): ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    Current subjects' list
                </div>

                <div class="panel-body">
                    <table class="table table-striped task-table">

                        <!-- Table Headings -->
                        <thead>
                            <th>Subject</th>
                            <th>Active?</th>
                            <th>Number of questions</th>
                            <th>Delete</th>
                        </thead>

                        <!-- Table Body -->
                        <tbody>
                            <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <?php
                                        if($subject->active == 1){
                                        $phraze = "active";
                                        } else {
                                        $phraze = "unactive";
                                        }
                                    ?>
                                    <td class="table-text">
                                        <div class="subj-name-unactive"><?php echo e($subject->name); ?></div>
                                        <div class="subj-name-active"><input type="text" class="textfieldSubjectVal" value="<?php echo e($subject->name); ?>"></div>
                                    </td>
                                    <td class="table-text">
                                        <div class="deactivateSubject" data-id="<?php echo e($subject->id); ?>" data-active="<?php echo e($subject->active); ?>"><?php echo e($phraze); ?></div>
                                    </td>
                                    <td class="table-text">
                                        <div><?php echo e($subject->questions_number); ?></div>
                                    </td>
                                    <td class="table-text">
                                        <div class="button-name-unactive"><button type="button" class="btn btn-warning start-success-name" data-id="<?php echo e($subject->id); ?>">Edit subj name</button></div>
                                        <div class="button-name-active"><button type="button" class="btn btn-success save-success-name" data-id="<?php echo e($subject->id); ?>">Save/Cancel</button></div>
                                    </td>
                                    <td>
                                    <?php if($subject->questions_number == 0): ?>
                                    <form action="/subject/<?php echo e($subject->id); ?>" method="POST">
                                        <?php echo e(csrf_field()); ?>

                                        <?php echo e(method_field('DELETE')); ?>

                                        <button class="btn btn-danger">Delete</button>
                                    </form>
                                    <?php else: ?>
                                        <button class="btn btn-default" style="cursor: no-drop">Delete</button>
                                    <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
        <form class="hiddenForm">
        <?php echo e(csrf_field()); ?>

        
   </form>
    </div>
    <!-- TODO: Current Tasks -->
    </main>
    <script>
      $( document ).ready(function() {
        $(".start-success-name").click(function(){
            console.log("edit quest clicked");
            $(this).css("display", "none");
            $(this).parent().parent().parent().find(".subj-name-unactive").css("display", "none");
            $(this).parent().parent().parent().find(".subj-name-active").css("display", "block");
            $(this).parent().parent().find(".button-name-active").css("display", "block");
        })

        $(".save-success-name").on("click", function(){
            var subjectValue = $(this).parent().parent().parent().find(".textfieldSubjectVal").val();
            var subjectIdValue = $(this).attr("data-id");
            var tokenValue = $(".hiddenForm input").val();
            var jqxhr = $.post( "/api/savesubject", {_token: tokenValue, subjectIdValue: subjectIdValue, subjectValue: subjectValue})
                .done(function(data) {
                    location.reload();
                })
                .fail(function() {
                    alert("Network error, please try again later");
                });
        })

        $(".deactivateSubject").on("click", function(){
            var subjectIdValue = $(this).attr("data-id");
            var subjectActiveValue = $(this).attr("data-active");
            var tokenValue = $(".hiddenForm input").val();
            console.log("tokenValue = ", tokenValue);
            console.log("subjectIdValue = ", subjectIdValue);
            var jqxhr = $.post( "/api/savesubjectactive", {_token: tokenValue, subjectIdValue: subjectIdValue, subjectActiveValue: subjectActiveValue})
                .done(function(data) {
                    location.reload();
                })
                .fail(function() {
                    alert("Network error, please try again later");
                });
        })
      })
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /mnt/c/Users/Peter/Code/homestead/resources/views/subjects.blade.php ENDPATH**/ ?>
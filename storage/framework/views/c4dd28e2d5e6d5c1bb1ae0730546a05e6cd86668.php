

<?php $__env->startSection('content'); ?>
    <main role="main" class="container">
    <h1 class="mt-3">Tests111</h1>
    <?php if(isset($id)): ?> 
      <?php if($id != 0 ): ?>
      <p>Maximum time you can spend on one question is 1 min. System informs you about time remaining. You can stop testing at any step. 
      System calculates amount of questions answered successfully and amount of failed questions. Please don't delete your browser's cookie during the testing. 
      We use cookie in order to collect information related to your testing. No personal information being collected. Press Start button to start testing or Cancel button to cancel. <br/><b>Happy testing!</b> </p>
      <label for="subjectsSelect">Subject selected:</label>
      <?php else: ?>
      <p>To use a basic features of our system such as testing, no registration needed. </p>
      <p>If you woul'd like to have an advantage of keeping statistics of your test results or adding your own questions, we'll ask you to register <a class="" href="<?php echo e(route('register')); ?>">here:</a><p>
      <label for="subjectsSelect">First, please select subject you want to start testing:</label>
      <?php endif; ?>
    <?php else: ?>
      <p>To use a basic features of our system such as testing, no registration needed. </p>
      <p>If you woul'd like to have an advantage of keeping statistics of your test results or adding your own questions, we'll ask you to register <a class="" href="<?php echo e(route('register')); ?>">here:</a><p>
      <label for="subjectsSelect">First, please select subject you want to start testing:</label>
    <?php endif; ?>

    <?php if(count($subjects) > 0): ?>
      <?php echo $__env->make('common.subjectsdropdown', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php endif; ?>

    <?php if(isset($id)): ?>
      <?php if($id != 0 ): ?>
        <button type="button" class="btn btn-primary" id="startTestingButton">Start</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <button type="button" class="btn btn-danger" id="cancelTestingButton">Cancel</button>
      <?php endif; ?>
    <?php endif; ?>


    </main>
    <script>
      $( document ).ready(function() {
        $( "#subjectsSelect" ).change(function() {
          window.location.href = "/tests/" + $( this ).val();
        });

        $( "#cancelTestingButton" ).click(function() {
          window.location.href = "/tests";
        });

        $( "#startTestingButton" ).click(function() {
          window.location.href = "/testing";
        });
      });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /mnt/c/Users/Peter/Code/homestead/resources/views/tests.blade.php ENDPATH**/ ?>
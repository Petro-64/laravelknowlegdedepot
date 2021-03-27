<div class="form-group">
    <select class="form-control" id="subjectsSelect">
    <option value="0">Please select</option>
    <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <option value="<?php echo e($subject->id); ?>"
        <?php if(isset($id)): ?>
            <?php if($subject->id == $id ?? ''): ?>
            selected
            <?php endif; ?>
        <?php endif; ?>
        ><?php echo e($subject->name); ?></option>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
</div><?php /**PATH /mnt/c/Users/Peter/Code/homestead/resources/views/common/subjectsdropdown.blade.php ENDPATH**/ ?>
<div class="form-group">
    <select class="form-control" id="subjectsSelect">
    <option value="0">Please select</option>
    @foreach ($subjects as $subject)
        <option value="{{ $subject->id }}"
        @isset($id)
            @if ($subject->id == $id ?? '')
            selected
            @endif
        @endisset
        >{{ $subject->name }}</option>
    @endforeach
    </select>
</div>
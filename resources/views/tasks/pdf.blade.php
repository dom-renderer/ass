@if(isset($task->parent->parent->checklist->id) && $task->parent->parent->checklist->is_point_checklist == 1)
    @include('tasks.pdf.dom')
@else
    @include('tasks.pdf.common')
@endif
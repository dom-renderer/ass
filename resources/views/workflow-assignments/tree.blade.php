@extends('layouts.app-master')

@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/orgchart/2.1.3/css/jquery.orgchart.min.css">
<style>
    #chart-container {
        position: relative;
        display: inline-block;
        height: 600px;
        width: 100%;
        border: 2px dashed #aaa;
        border-radius: 5px;
        overflow: auto;
        text-align: center;
        background-color: #f9f9f9;
    }

    .orgchart {
        background: #f9f9f9;
    }

    .orgchart .node {
        min-width: 350px;
    }

    .orgchart .node .title {
        background-color: #0d5d31;
        color: #fff;
        height: 40px;
        line-height: 40px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-weight: bold;
    }

    .orgchart .node .content {
        border: 1px solid #0d5d31;
        background-color: #fff;
        color: #333;
        padding: 10px;
        height: auto;
        text-align: left;
        font-size: 12px;
    }
    
    .orgchart .node .content ul {
        padding-left: 20px;
        margin: 0;
    }
    
    .orgchart .node .content li {
        margin-bottom: 5px;
    }

    /* Root node specific styles */
    .orgchart .node.root-node .title {
        background-color: #1a1a2e;
    }
    
    .orgchart .node.root-node .content {
        border-color: #1a1a2e;
        display: none; /* Hide content for root if it's empty */
    }

    .orgchart .verticalNodes > td::before {
        /* border-color:transparent!important; */
    }

    .orgchart .verticalNodes ul>li::after, .orgchart .verticalNodes ul>li::before {
        /* border-color:transparent!important; */
    }

    .content > li {
        word-break: break-all;
    }
    
    .bubble-overlay {
        position: fixed;
        inset: 0;
        pointer-events: none;
        z-index: 9999;
    }

    .bubble-cluster {
        position: absolute;
        pointer-events: auto;
    }

    .bubble-tag {
        position: absolute;
        padding: 8px 14px;
        background: #fff;
        color: #000;
        border: 3px solid rgba(238,217,54,.5);
        border-radius: 999px;
        font-weight:600;
        font-size: 15px;
        white-space: nowrap;
        transform: scale(0);
        opacity: 0;
        transition: 
            transform 0.6s cubic-bezier(.2,1.4,.3,1),
            opacity 0.3s ease;
        box-shadow: 0 0 12px rgba(0,255,213,0.25);
    }

    .bubble-tag.show {
        opacity: 1;
        transform:
            translate(var(--x), var(--y))
            scale(1);
    }      
</style>
@endpush

@section('content')
<div class="p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>{{ $assignment->title }}</h2>
            <p class="text-muted">Workflow Tree Visualization</p>
        </div>
        <div>
            <a href="{{ route('workflow-assignments.show', encrypt($assignment->id)) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Details
            </a>
            <a href="{{ route("workflow-assignments.table", encrypt($assignment->id)) }}" class="btn btn-success">
                <i class="bi bi-table me-2"></i>Table View
            </a>
        </div>
    </div>

    <div id="chart-container"></div>
</div>
@endsection

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/orgchart/2.1.3/js/jquery.orgchart.min.js"></script>
<script>
    $(document).ready(function() {
        var dataUrl = '{{ route("workflow-assignments.tree-data", encrypt($assignment->id)) }}';

        $.ajax({
            url: dataUrl,
            dataType: 'json',
            success: function(data) {
                $('#chart-container').orgchart({
                    'data' : data,
                    'nodeContent': 'content',
                    'visibleLevel': 2,
                    'pan': true,
                    'zoom': true,
                    'createNode': function($node, data) {
                        if (data.steps && Array.isArray(data.steps) && data.steps.length > 0) {
                            var listHtml = '<ul style="text-align:left; padding-left:20px; margin:0;">';
                            data.steps.forEach(function(step, index) {
                                listHtml += '<li>' + step + '</li>';
                            });
                            listHtml += '</ul>';
                            $node.find('.content').html(listHtml);
                        } else if (data.className === 'root-node') {
                             $node.find('.content').remove();
                        }
                    }
                });
            },
            error: function(xhr, status, error) {
                console.error("Error fetching tree data:", error);
                $('#chart-container').html('<div class="alert alert-danger m-5">Error loading workflow data. Please try again.</div>');
            }
        });

        let bubbleOverlay;

        function closeBubbles() {
            if (bubbleOverlay) {
                bubbleOverlay.remove();
                bubbleOverlay = null;
            }
        }

        $(document).on('click', '[data-bubbles]', function (e) {
            e.stopPropagation();
            closeBubbles();

            let tags = this.dataset.json !== undefined && typeof this.dataset.json == 'string' ? this.dataset.json : '';
            tags = tags.split(',');
            if (!tags.length) return;

            const rect = this.getBoundingClientRect();

            bubbleOverlay = document.createElement('div');
            bubbleOverlay.className = 'bubble-overlay';
            document.body.appendChild(bubbleOverlay);

            const cluster = document.createElement('div');
            cluster.className = 'bubble-cluster';
            cluster.style.left = rect.left + rect.width / 2 + 'px';
            cluster.style.top = rect.top + rect.height / 2 + 'px';
            bubbleOverlay.appendChild(cluster);

            const count = tags.length;
            const radius = 90;

            tags.forEach((label, i) => {
                const bubble = document.createElement('div');
                bubble.className = 'bubble-tag';
                bubble.textContent = label;

                const angle = (Math.PI * 2 / count) * i;
                bubble.style.setProperty('--x', Math.cos(angle) * radius + 'px');
                bubble.style.setProperty('--y', Math.sin(angle) * radius + 'px');

                cluster.appendChild(bubble);

                requestAnimationFrame(() => bubble.classList.add('show'));
            });
        });

        $(document).on('click', closeBubbles);
    });
</script>
@endpush

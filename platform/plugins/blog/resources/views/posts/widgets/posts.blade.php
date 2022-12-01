@if ($posts->count() > 0)
    <div class="scroller">
        <table class="table table-striped">
            <thead>
            <tr>
                <th>#</th>
                <th>{{ trans('core/base::tables.name') }}</th>
                <th>{{ trans('core/base::tables.created_at') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($posts as $post)
                <tr>
                    <td>{{ $loop->index + 1 }}</td>
                    <td><a href="{!! route("posts.edit", [$post->id]) !!}">{!! $post->name !!}</a></td>
                    <td>{{ BaseHelper::formatDate($post->created_at, 'd-m-Y') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @if ($posts instanceof Illuminate\Pagination\LengthAwarePaginator)
        <div class="widget_footer">
            @include('core/dashboard::partials.paginate', ['data' => $posts, 'limit' => $limit])
        </div>
    @endif
@else
    @include('core/dashboard::partials.no-data', ['message' => trans('plugins/blog::posts.no_new_post_now')])
@endif

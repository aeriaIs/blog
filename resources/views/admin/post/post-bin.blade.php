@extends('layouts.backend.home')

@section('title', 'Post Recycle Bin')

@section('content')
<table class="table table-hover table-light table-bordered">
	<thead class="table-primary">
		<tr>
			<th scope="col">No.</th>
			<th scope="col">Title</th>
			<th scope="col">Tags</th>
			<th scope="col">Thumbnail</th>
			<th scope="col">Action</th>
		</tr>
	</thead>
	<tbody>
		@forelse($posts as $key => $post)
		<tr>
			<td>{{ $key+=1 }}</td>
			<td>{{ $post->title }}</td>
			<td>
				<ul>
					@foreach($post->tags as $tag)
					<li>{{ $tag->name }}</li>
					@endforeach
				</ul>
			</td>
			<td><img src="{{ asset('storage/post-image/'. $post->image) }}" class="img-fluid" width="99"></td>
			<td>
				<a href="{{ route('post.restore', $post->id) }}" class="btn btn-sm btn-primary">Restore</a>
				<button class="btn btn-danger btn-flat btn-sm remove-post" data-id="{{ $post->id }}" data-action="{{ route('post.clean',$post->id) }}"> Delete </button>
			</td>
		</tr>
		@empty
			<td>Tidak ada data.</td>
		@endforelse
	</tbody>
</table>

{{ $posts->links() }}

@endsection

@section('js')
<script type="text/javascript">
  $('body').on("click",".remove-post",function(){
    var current_object = $(this);
    swal({
        title: "Apakah anda Yakin?",
        text: "Post yang akan dihapus tidak akan bisa di restore!",
        type: "error",
        showCancelButton: true,
        dangerMode: true,
        cancelButtonClass: '#ffffff',
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Hapus',
    },function (result) {
        if (result) {
            var action = current_object.attr('data-action');
            var token = jQuery('meta[name="csrf-token"]').attr('content');
            var id = current_object.attr('data-id');

            $('body').html("<form class='form-inline remove-form' method='post' action='"+action+"'></form>");
            $('body').find('.remove-form').append('<input name="_method" type="hidden" value="delete">');
            $('body').find('.remove-form').append('<input name="_token" type="hidden" value="'+token+'">');
            $('body').find('.remove-form').append('<input name="id" type="hidden" value="'+id+'">');
            $('body').find('.remove-form').submit();
        }
    });
});
</script>
@endsection
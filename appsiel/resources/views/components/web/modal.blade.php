@if($modal != null)
<style>

.modal-content{
    border-radius: 20px;
    @if($modal->path != null || $modal->path!='')
        background: url('{{$modal->path}}') no-repeat;
        height: 100%;
        object-fit: cover;
        object-position: center center;
    @endif
}

</style>

<div id="modal">
    <!-- Modal -->
    <div class="modal fade" style="overflow-y: hidden;" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="position: relative;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="position: absolute; right: 10px; font-size: 35px; border-radius: 50%; color: white; padding: 5px; z-index: 1000; cursor: pointer;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div class="modal-body" style="padding: 0;">
                    <div class="col-md-12" style="padding: 40px; color: #000; text-align: justify;">
                        <h3>{{$modal->title}}</h3>
                        <p>{{$modal->body}}</p>
                        @if($modal->enlace!=null || $modal->enlace!='')
                        <a target="_blank" class="btn btn-primary" href="{{$modal->enlace}}">Conoce más...</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
   window.onload = () => {
       setTimeout(()=>{
            $('#exampleModal').modal('show');
       },30);
   };
</script>

@endif
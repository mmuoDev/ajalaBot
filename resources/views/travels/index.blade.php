@extends('layouts.main-menu')
@section('styles')
    <style>
        #map2 {
            height: 100%;
        }
    </style>
@stop
@section('contents')
    <div class="content-wrapper">

        <!-- Main content -->
        <section class="content">
            <!-- Info boxes -->
            <hr/>
            <!--Error messages -->
            <!-- Main row -->
            <div class="row">
                <!-- Left col -->
                <div class="col-md-12">
                    <!-- MAP & BOX PANE -->
                    <div class="box box-warning">
                        <div class="box-header with-border">
                            <h3 class="box-title">All Trips/Tours</h3>
                        </div>
                        <!-- /.box-header -->

                            <div class="box-body">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <table class="table table-responsive" id="tables">
                                            <thead>
                                            <tr>
                                                <th>S/N</th>
                                                <th>Date Added</th>
                                                <th>Category</th>
                                                <th>Header</th>
                                                <th>Details</th>
                                                <th>Deadline</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @if(isset($travels))
                                                <?php $i = 1; ?>
                                                @foreach($travels as $travel)
                                                    <?php
                                                        $created = date('d-m-Y', strtotime($travel->created));
                                                        $deadline = date('d-m-Y', strtotime($travel->deadline));
                                                    ?>
                                                    <tr>
                                                        <td>{{$i++}}</td>
                                                        <td>{{$created}}</td>
                                                        <td>{{ucfirst($travel->category)}}</td>
                                                        <td>{{ucfirst($travel->header)}}</td>
                                                        <td>{{$travel->details}}</td>
                                                        <td>{{$deadline}}</td>
                                                        <td>{{ucfirst($travel->status)}}</td>
                                                        <td>
                                                            <a  href="{{url('travels/edit/'.$travel->uri)}}" title="Edit Trip/Tour" class="btn btn-default btn-sm"><i class="fa fa-edit"></i></a>
                                                            <button type="button" title="Delete Trip" data-toggle="modal" data-target="#{{$travel->travel_id}}" class="btn btn-default btn-sm"><i class="fa fa-trash"></i></button>
                                                        </td>

                                                    </tr>
                                                    <!-- /.mail-box-messages -->
                                                    <div class="modal fade" id="{{$travel->travel_id}}">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span></button>
                                                                    <h4 class="modal-title">{{$travel->header}} [Delete]</h4>

                                                                </div>
                                                                <form class="" method="post" action="">
                                                                    {{csrf_field()}}
                                                                    <input type="hidden" name="travel_id" value="{{$travel->travel_id}}">
                                                                    <div class="modal-body">
                                                                        <p>
                                                                            Are you sure you want to delete this tour/trip?
                                                                        </p>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">No</button>
                                                                        <input type="submit" name="submit" value="Yes" class="btn btn-primary">
                                                                    </div>
                                                                </form>
                                                            </div>
                                                            <!-- /.modal-content -->
                                                        </div>
                                                        <!-- /.modal-dialog -->
                                                    </div>
                                                    <!-- /.modal -->
                                                @endforeach
                                            @endif

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                    </div>

                </div>
            </div>
            <!-- /.row -->
        </section>
    </div>
@stop
@section('scripts')
    <script>
        //Google Maps

    </script>
@stop


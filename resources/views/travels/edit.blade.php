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
                            <h3 class="box-title">Edit Trip/Tour</h3>
                        </div>
                        <!-- /.box-header -->
                        <form role="form" method="post" action="" accept-charset="UTF-8" id="users-form" enctype="multipart/form-data">
                            {{csrf_field()}}
                            @include('partials.errors')
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <span>Editing your trip/tour will it invisible to Ajala until the updates have been approved.</span>
                                    </div>
                                </div>
                                <div class="row">
                                    <input type="hidden" name="travel_id" value="{{$travels->id}}">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="exampleInputN">Header</label>
                                            <input type="text" class="form-control" id="exampleInputN"
                                                   placeholder="Give your tour/trip a header!" name="header" required value="{{$travels->header}}">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="text-box form-group">
                                            <label for="exampleInputFile">Select Category</label>
                                            <select name="category_id" id="input" class="form-control" required="required">
                                                <option value="">--Please select--</option>
                                                @if(isset($categories))
                                                    @foreach($categories as $category)
                                                        <option value="{{ $category->id }}" @if($category->id == $travels->category_id) {{'selected'}} @endif>{{ucfirst($category->category)}}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="exampleInputFile">Start Date</label>
                                            <input type="text" required name="start_date"  class="form-control datepicker" value="{{date('d/m/Y', strtotime($travels->start_date))}}">
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="exampleInputFile">End Date</label>
                                            <input type="text" name="end_date" class="form-control datepicker" value="{{date('d/m/Y', strtotime($travels->end_date))}}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="text-box form-group">
                                            <label for="exampleInputFile">Tour/Trip Description</label>
                                            <textarea name="details" class="form-control" required maxlength="100">{{$travels->details}}</textarea>
                                            <span class="help-block">Keep it as short as possible. Maximum length is 100 characters</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="exampleInputFile">Deadline for Booking</label>
                                            <input type="text" required name="deadline"  class="form-control datepicker" value="{{date('d/m/Y', strtotime($travels->deadline))}}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="exampleInputFile">Single Price (In Naira)</label>
                                            <input type="text" required  name="single_price"  class="form-control" value="{{$travels->single_price}}">
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="exampleInputFile">Couple Price (In Naira)</label>
                                            <input type="text" name="couple_price" class="form-control" value="{{$travels->couple_price}}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="well">
                                            <!--Show current image -->
                                            <?php
                                                $file = \App\Libraries\Utilities::getTravelFiles($travels->id);
                                            ?>
                                            @if(!empty($file))
                                                <a href="{{url('travels/download-file/'.$file[0]->file_uri)}}" target="_blank">View Current Image</a>
                                            @endif
                                            <div class="text-box form-group">
                                                <label for="exampleInputFile">Update Promotional Image </label>
                                                <input type="file"  id="exampleInputFile" name="files" id="imageinput"  accept="application/pdf, image/*">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="box-footer">
                                <center>
                                    <button type="submit" class="btn btn-success">Send for Approval</button>
                                </center>
                            </div>
                        </form>
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
        $('.datepicker').datepicker({ dateFormat: 'dd/mm/yy' });
        // $('#from').datepicker({ dateFormat: 'dd/mm/yy' });
    </script>
@stop

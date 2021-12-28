@extends('layouts.layout')

@section('title', 'Form')

@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Basic Form</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="index.html">Home</a>
            </li>
            <li class="breadcrumb-item">
                <a>Forms</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>Basic Form</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">

    </div>
</div>
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <h5>All form elements <small>With custom checbox and radion elements.</small></h5>
                        <div class="ibox-tools">
                            <a class="collapse-link">
                                <i class="fa fa-chevron-up"></i>
                            </a>
                            <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                <i class="fa fa-wrench"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-user">
                                <li><a href="#" class="dropdown-item">Config option 1</a>
                                </li>
                                <li><a href="#" class="dropdown-item">Config option 2</a>
                                </li>
                            </ul>
                            <a class="close-link">
                                <i class="fa fa-times"></i>
                            </a>
                        </div>
                    </div>
                    <div class="ibox-content">
                        <form method="get">
                            <div class="form-group  row"><label class="col-sm-2 col-form-label">Normal</label>

                                <div class="col-sm-10"><input type="text" class="form-control"></div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Help text</label>
                                <div class="col-sm-10"><input type="text" class="form-control"> <span class="form-text m-b-none">A block of help text that breaks onto a new line and may extend beyond one line.</span>
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Password</label>

                                <div class="col-sm-10"><input type="password" class="form-control" name="password"></div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Placeholder</label>

                                <div class="col-sm-10"><input type="text" placeholder="placeholder" class="form-control"></div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group row"><label class="col-lg-2 col-form-label">Disabled</label>

                                <div class="col-lg-10"><input type="text" disabled="" placeholder="Disabled input here..." class="form-control"></div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group row"><label class="col-lg-2 col-form-label">Static control</label>

                                <div class="col-lg-10"><p class="form-control-static">email@example.com</p></div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Checkboxes and radios <br/>
                                <small class="text-navy">Normal Bootstrap elements</small></label>

                                <div class="col-sm-10">
                                    <div><label> <input type="checkbox" value=""> Option one is this and that&mdash;be sure to include why it's great </label></div>
                                    <div><label> <input type="radio" checked="" value="option1" id="optionsRadios1" name="optionsRadios"> Option one is this and that&mdash;be sure to
                                        include why it's great </label></div>
                                    <div><label> <input type="radio" value="option2" id="optionsRadios2" name="optionsRadios"> Option two can be something else and selecting it will
                                        deselect option one </label></div>
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Inline checkboxes</label>

                                <div class="col-sm-10"><label> <input type="checkbox" value="option1" id="inlineCheckbox1"> a </label> <label class="checkbox-inline">
                                    <input type="checkbox" value="option2" id="inlineCheckbox2"> b </label> <label>
                                    <input type="checkbox" value="option3" id="inlineCheckbox3"> c </label></div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Checkboxes &amp; radios <br/><small class="text-navy">Custom elements</small></label>

                                <div class="col-sm-10">
                                    <div class="i-checks"><label> <input type="checkbox" value=""> <i></i> Option one </label></div>
                                    <div class="i-checks"><label> <input type="checkbox" value="" checked=""> <i></i> Option two checked </label></div>
                                    <div class="i-checks"><label> <input type="checkbox" value="" disabled="" checked=""> <i></i> Option three checked and disabled </label></div>
                                    <div class="i-checks"><label> <input type="checkbox" value="" disabled=""> <i></i> Option four disabled </label></div>
                                    <div class="i-checks"><label> <input type="radio" value="option1" name="a"> <i></i> Option one </label></div>
                                    <div class="i-checks"><label> <input type="radio" checked="" value="option2" name="a"> <i></i> Option two checked </label></div>
                                    <div class="i-checks"><label> <input type="radio" disabled="" checked="" value="option2"> <i></i> Option three checked and disabled </label></div>
                                    <div class="i-checks"><label> <input type="radio" disabled="" name="a"> <i></i> Option four disabled </label></div>
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Inline checkboxes</label>

                                <div class="col-sm-10"><label class="checkbox-inline i-checks"> <input type="checkbox" value="option1">a </label>
                                    <label class="i-checks"> <input type="checkbox" value="option2"> b </label>
                                    <label class="i-checks"> <input type="checkbox" value="option3"> c </label></div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Select</label>

                                <div class="col-sm-10"><select class="form-control m-b" name="account">
                                    <option>option 1</option>
                                    <option>option 2</option>
                                    <option>option 3</option>
                                    <option>option 4</option>
                                </select>

                                    <div class="col-lg-4 m-l-n"><select class="form-control" multiple="">
                                        <option>option 1</option>
                                        <option>option 2</option>
                                        <option>option 3</option>
                                        <option>option 4</option>
                                    </select></div>
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group row has-success"><label class="col-sm-2 col-form-label">Input with success</label>

                                <div class="col-sm-10"><input type="text" class="form-control"></div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group row has-warning"><label class="col-sm-2 col-form-label">Input with warning</label>

                                <div class="col-sm-10"><input type="text" class="form-control"></div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group  row has-error"><label class="col-sm-2 col-form-label">Input with error</label>

                                <div class="col-sm-10"><input type="text" class="form-control"></div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Control sizing</label>

                                <div class="col-sm-10"><input type="text" placeholder=".form-control-lg" class="form-control form-control-lg m-b">
                                    <input type="text" placeholder="Default input" class="form-control m-b"> <input type="text" placeholder=".form-control-sm" class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Column sizing</label>

                                <div class="col-sm-10">
                                    <div class="row">
                                        <div class="col-md-2"><input type="text" placeholder=".col-md-2" class="form-control"></div>
                                        <div class="col-md-3"><input type="text" placeholder=".col-md-3" class="form-control"></div>
                                        <div class="col-md-4"><input type="text" placeholder=".col-md-4" class="form-control"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Select 2 Single</label>

                                <div class="col-sm-10">
                                    <div class="row">
                                        <select class="select2_demo_3 form-control">
                                            <option></option>
                                            <option value="Bahamas">Bahamas</option>
                                            <option value="Bahrain">Bahrain</option>
                                            <option value="Bangladesh">Bangladesh</option>
                                            <option value="Barbados">Barbados</option>
                                            <option value="Belarus">Belarus</option>
                                            <option value="Belgium">Belgium</option>
                                            <option value="Belize">Belize</option>
                                            <option value="Benin">Benin</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Select 2 Multiple</label>

                                <div class="col-sm-10">
                                    <div class="row">
                                        <select class="select2_demo_2 form-control" multiple="multiple">
                                            <option value="Mayotte">Mayotte</option>
                                            <option value="Mexico">Mexico</option>
                                            <option value="Micronesia, Federated States of">Micronesia, Federated States of</option>
                                            <option value="Moldova, Republic of">Moldova, Republic of</option>
                                            <option value="Monaco">Monaco</option>
                                            <option value="Mongolia">Mongolia</option>
                                            <option value="Montenegro">Montenegro</option>
                                            <option value="Montserrat">Montserrat</option>
                                            <option value="Morocco">Morocco</option>
                                            <option value="Mozambique">Mozambique</option>
                                            <option value="Myanmar">Myanmar</option>
                                            <option value="Namibia">Namibia</option>
                                            <option value="Nauru">Nauru</option>
                                            <option value="Nepal">Nepal</option>
                                            <option value="Netherlands">Netherlands</option>
                                            <option value="New Caledonia">New Caledonia</option>
                                            <option value="New Zealand">New Zealand</option>
                                            <option value="Nicaragua">Nicaragua</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Tanggal</label>

                                <div class="col-sm-10">
                                    <div class="row">
                                        <div class="input-group date">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input type="text" class="form-control" value="03/04/2014">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Rentang Tanggal</label>

                                <div class="col-sm-10">
                                    <div class="row">
                                        <div class="input-daterange input-group" id="datepicker">
                                            <input type="text" class="form-control-sm form-control" name="start" value="05/14/2014"/>
                                            <span class="input-group-addon">to</span>
                                            <input type="text" class="form-control-sm form-control" name="end" value="05/22/2014" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            
                            <div class="hr-line-dashed"></div>
                            
                            <div class="hr-line-dashed"></div>
                            <div class="form-group row">
                                <div class="col-sm-4 col-sm-offset-2">
                                    <button class="btn btn-white btn-sm" type="submit">Cancel</button>
                                    <button class="btn btn-primary btn-sm" type="submit">Save changes</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
            </div>
@endsection
@push('scripts')
<script>
    $(document).ready(function () {
        $('.i-checks').iCheck({
            checkboxClass: 'icheckbox_square-green',
            radioClass: 'iradio_square-green',
        });
            $(".select2_demo_2").select2();
            $(".select2_demo_3").select2({
                placeholder: "Select a state",
                allowClear: true
            });
            $('.input-group.date').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
                format: "dd/mm/yyyy"
            });

            

            $('.input-daterange').datepicker({
                keyboardNavigation: false,
                forceParse: false,
                autoclose: true
            });

    });
</script>
@endpush
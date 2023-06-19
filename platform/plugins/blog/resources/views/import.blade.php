@extends('core/base::layouts.master')
@section('content')
    {!! Form::open(['class' => 'form-import-data', 'files' => 'true']) !!}
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-sm-12">
<!--                <div class="alert alert-warning alert-dismissible my-2 hidden fade show" role="alert" data-alert-id="bulk-import">
                    <strong>{{ trans('plugins/blog::bulk-import.note') }}</strong>
                    <span>{{ trans('plugins/blog::bulk-import.warning_before_importing') }}</span>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>-->
                <div class="widget meta-boxes">
                    <div class="widget-title pl-2">
                        <h4>{{ trans('plugins/blog::bulk-import.menu') }}</h4>
                    </div>
                    <div class="widget-body">
                        <div class="form-group @if ($errors->has('file')) has-error @endif">
                            <label class="control-label required" for="input-group-file">
                                {{ trans('plugins/blog::bulk-import.choose_file')}}
                            </label>
                            <div class="custom-file">
                                {!! Form::file('file', [
                                    'required'         => true,
                                    'class'            => 'custom-file-input',
                                    'id'               => 'input-group-file',
                                    'aria-describedby' => 'input-group-addon',
                                ]) !!}
                                <label class="custom-file-label" id="custom-file-label" for="input-group-file">
                                    (.csv,.xls,.xlsx)
                                </label>
                            </div>
                            {!! Form::error('file', $errors) !!}
                            <div class="mt-3 text-center p-2 border bg-light">
                                <a href="{!! generateAsset('vendor/core/plugins/blog/glossary_template.csv') !!}" class="download-template">
                                    <i class="fas fa-file-csv"></i>
                                    {{ trans('plugins/blog::bulk-import.download-csv-file') }}
                                </a> &nbsp; | &nbsp;
                                <a href="{!! generateAsset('vendor/core/plugins/blog/glossary_template.xlsx') !!}" class="download-template">
                                    <i class="fas fa-file-excel"></i>
                                    {{ trans('plugins/blog::bulk-import.download-excel-file') }}
                                </a>
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-info btn-block"
                                    data-choose-file="{{ trans('plugins/blog::bulk-import.please_choose_the_file')}}"
                                    data-loading-text="{{ trans('plugins/blog::bulk-import.loading_text') }}"
                                    data-complete-text="{{ trans('plugins/blog::bulk-import.imported_successfully') }}"
                                    id="input-group-addon">
                                {{ trans('plugins/blog::bulk-import.start_import') }}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="hidden main-form-message">
                    <p id="imported-message"></p>
                    <div class="show-errors hidden">
                        <h3 class="text-warning text-center">{{ trans('plugins/blog::bulk-import.failures') }}</h3>
                        <ul id="imported-listing"></ul>
                    </div>
                </div>
            </div>
        </div>
    {!! Form::close() !!}

    <div class="widget meta-boxes">
        <div class="widget-title pl-2">
            <h4 class="text-info">{{ trans('plugins/blog::bulk-import.template') }}</h4>
        </div>
        <div class="widget-body">
            <div class="table-responsive">
                <table class="table text-left table-striped table-bordered">
                    <thead>
                    <tr>
                        <th scope="col">Keyword</th>
                        <th scope="col">Description</th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Ape-ing</td>
                            <td>Ape-ing nghĩa là ...</td>
                        </tr>
                        <tr>
                            <td>Ape-ing</td>
                            <td>Ape-ing nghĩa là ...</td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="widget meta-boxes mt-4">
        <div class="widget-title pl-2">
            <h4 class="text-info">{{ trans('plugins/blog::bulk-import.rules') }}</h4>
        </div>
        <div class="widget-body">
            <table class="table text-left table-bordered">
                <thead>
                    <tr>
                        <th scope="col">Column</th>
                        <th scope="col">Rules</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th scope="row">Keyword</th>
                        <td>(required)</td>
                    </tr>
                    <tr>
                        <th scope="row">Description</th>
                        <td>(nullable)</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@stop

@extends('layouts.master')

@section('content')
<!-- General UI Elements -->
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        General UI
        <small>Preview of UI elements</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">UI</a></li>
        <li class="active">General</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content">
      <!-- COLOR PALETTE -->
      <div class="box box-default color-palette-box">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-tag"></i> Color Palette</h3>
        </div>
        <div class="box-body">
          <div class="row">
            <div class="col-sm-4 col-md-2">
              <h4 class="text-center">Primary</h4>

              <div class="color-palette-set">
                <div class="bg-light-blue disabled color-palette"><span>Disabled</span></div>
                <div class="bg-light-blue color-palette"><span>#3c8dbc</span></div>
                <div class="bg-light-blue-active color-palette"><span>Active</span></div>
              </div>
            </div>
            <!-- /.col -->
            <div class="col-sm-4 col-md-2">
              <h4 class="text-center">Info</h4>

              <div class="color-palette-set">
                <div class="bg-aqua disabled color-palette"><span>Disabled</span></div>
                <div class="bg-aqua color-palette"><span>#00c0ef</span></div>
                <div class="bg-aqua-active color-palette"><span>Active</span></div>
              </div>
            </div>
            <!-- /.col -->
            <div class="col-sm-4 col-md-2">
              <h4 class="text-center">Success</h4>

              <div class="color-palette-set">
                <div class="bg-green disabled color-palette"><span>Disabled</span></div>
                <div class="bg-green color-palette"><span>#00a65a</span></div>
                <div class="bg-green-active color-palette"><span>Active</span></div>
              </div>
            </div>
            <!-- /.col -->
            <div class="col-sm-4 col-md-2">
              <h4 class="text-center">Warning</h4>

              <div class="color-palette-set">
                <div class="bg-yellow disabled color-palette"><span>Disabled</span></div>
                <div class="bg-yellow color-palette"><span>#f39c12</span></div>
                <div class="bg-yellow-active color-palette"><span>Active</span></div>
              </div>
            </div>
            <!-- /.col -->
            <div class="col-sm-4 col-md-2">
              <h4 class="text-center">Danger</h4>

              <div class="color-palette-set">
                <div class="bg-red disabled color-palette"><span>Disabled</span></div>
                <div class="bg-red color-palette"><span>#f56954</span></div>
                <div class="bg-red-active color-palette"><span>Active</span></div>
              </div>
            </div>
            <!-- /.col -->
            <div class="col-sm-4 col-md-2">
              <h4 class="text-center">Gray</h4>

              <div class="color-palette-set">
                <div class="bg-gray disabled color-palette"><span>Disabled</span></div>
                <div class="bg-gray color-palette"><span>#d2d6de</span></div>
                <div class="bg-gray-active color-palette"><span>Active</span></div>
              </div>
            </div>
            <!-- /.col -->
          </div>
          <!-- /.row -->
          <div class="row">
            <div class="col-sm-4 col-md-2">
              <h4 class="text-center">Navy</h4>

              <div class="color-palette-set">
                <div class="bg-navy disabled color-palette"><span>Disabled</span></div>
                <div class="bg-navy color-palette"><span>#001F3F</span></div>
                <div class="bg-navy-active color-palette"><span>Active</span></div>
              </div>
            </div>
            <!-- /.col -->
            <div class="col-sm-4 col-md-2">
              <h4 class="text-center">Teal</h4>

              <div class="color-palette-set">
                <div class="bg-teal disabled color-palette"><span>Disabled</span></div>
                <div class="bg-teal color-palette"><span>#39CCCC</span></div>
                <div class="bg-teal-active color-palette"><span>Active</span></div>
              </div>
            </div>
            <!-- /.col -->
            <div class="col-sm-4 col-md-2">
              <h4 class="text-center">Purple</h4>

              <div class="color-palette-set">
                <div class="bg-purple disabled color-palette"><span>Disabled</span></div>
                <div class="bg-purple color-palette"><span>#605ca8</span></div>
                <div class="bg-purple-active color-palette"><span>Active</span></div>
              </div>
            </div>
            <!-- /.col -->
            <div class="col-sm-4 col-md-2">
              <h4 class="text-center">Orange</h4>

              <div class="color-palette-set">
                <div class="bg-orange disabled color-palette"><span>Disabled</span></div>
                <div class="bg-orange color-palette"><span>#ff851b</span></div>
                <div class="bg-orange-active color-palette"><span>Active</span></div>
              </div>
            </div>
            <!-- /.col -->
            <div class="col-sm-4 col-md-2">
              <h4 class="text-center">Maroon</h4>

              <div class="color-palette-set">
                <div class="bg-maroon disabled color-palette"><span>Disabled</span></div>
                <div class="bg-maroon color-palette"><span>#D81B60</span></div>
                <div class="bg-maroon-active color-palette"><span>Active</span></div>
              </div>
            </div>
            <!-- /.col -->
            <div class="col-sm-4 col-md-2">
              <h4 class="text-center">Black</h4>

              <div class="color-palette-set">
                <div class="bg-black disabled color-palette"><span>Disabled</span></div>
                <div class="bg-black color-palette"><span>#111111</span></div>
                <div class="bg-black-active color-palette"><span>Active</span></div>
              </div>
            </div>
            <!-- /.col -->
          </div>
          <!-- /.row -->
        </div>
        <!-- /.box-body -->
      </div>
      <!-- /.box -->
      <!-- START ALERTS AND CALLOUTS -->
      <h2 class="page-header">Alerts and Callouts</h2>

      <div class="row">
        <div class="col-md-6">
          <div class="box box-default">
            <div class="box-header with-border">
              <i class="fa fa-warning"></i>

              <h3 class="box-title">Alerts</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h4><i class="icon fa fa-ban"></i> Alert!</h4>
                Danger alert preview. This alert is dismissable. A wonderful serenity has taken possession of my entire
                soul, like these sweet mornings of spring which I enjoy with my whole heart.
              </div>
              <div class="alert alert-info alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h4><i class="icon fa fa-info"></i> Alert!</h4>
                Info alert preview. This alert is dismissable.
              </div>
              <div class="alert alert-warning alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h4><i class="icon fa fa-warning"></i> Alert!</h4>
                Warning alert preview. This alert is dismissable.
              </div>
              <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h4><i class="icon fa fa-check"></i> Alert!</h4>
                Success alert preview. This alert is dismissable.
              </div>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->

        <div class="col-md-6">
          <div class="box box-default">
            <div class="box-header with-border">
              <i class="fa fa-bullhorn"></i>

              <h3 class="box-title">Callouts</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <div class="callout callout-danger">
                <h4>I am a danger callout!</h4>

                <p>There is a problem that we need to fix. A wonderful serenity has taken possession of my entire soul,
                  like these sweet mornings of spring which I enjoy with my whole heart.</p>
              </div>
              <div class="callout callout-info">
                <h4>I am an info callout!</h4>

                <p>Follow the steps to continue to payment.</p>
              </div>
              <div class="callout callout-warning">
                <h4>I am a warning callout!</h4>

                <p>This is a yellow callout.</p>
              </div>
              <div class="callout callout-success">
                <h4>I am a success callout!</h4>

                <p>This is a green callout.</p>
              </div>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
      <!-- END ALERTS AND CALLOUTS -->
      <!-- START CUSTOM TABS -->
      <h2 class="page-header">AdminLTE Custom Tabs</h2>

      <div class="row">
        <div class="col-md-6">
          <!-- Custom Tabs -->
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#tab_1" data-toggle="tab">Tab 1</a></li>
              <li><a href="#tab_2" data-toggle="tab">Tab 2</a></li>
              <li><a href="#tab_3" data-toggle="tab">Tab 3</a></li>
              <li class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                  Dropdown <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                  <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Action</a></li>
                  <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Another action</a></li>
                  <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Something else here</a></li>
                  <li role="presentation" class="divider"></li>
                  <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Separated link</a></li>
                </ul>
              </li>
              <li class="pull-right"><a href="#" class="text-muted"><i class="fa fa-gear"></i></a></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane active" id="tab_1">
                <b>How to use:</b>

                <p>Exactly like the original bootstrap tabs except you should use
                  the custom wrapper <code>.nav-tabs-custom</code> to achieve this style.</p>
                A wonderful serenity has taken possession of my entire soul,
                like these sweet mornings of spring which I enjoy with my whole heart.
                I am alone, and feel the charm of existence in this spot,
                which was created for the bliss of souls like mine. I am so happy,
                my dear friend, so absorbed in the exquisite sense of mere tranquil existence,
                that I neglect my talents. I should be incapable of drawing a single stroke
                at the present moment; and yet I feel that I never was a greater artist than now.
              </div>
              <!-- /.tab-pane -->
              <div class="tab-pane" id="tab_2">
                The European languages are members of the same family. Their separate existence is a myth.
                For science, music, sport, etc, Europe uses the same vocabulary. The languages only differ
                in their grammar, their pronunciation and their most common words. Everyone realizes why a
                new common language would be desirable: one could refuse to pay expensive translators. To
                achieve this, it would be necessary to have uniform grammar, pronunciation and more common
                words. If several languages coalesce, the grammar of the resulting language is more simple
                and regular than that of the individual languages.
              </div>
              <!-- /.tab-pane -->
              <div class="tab-pane" id="tab_3">
                Lorem Ipsum is simply dummy text of the printing and typesetting industry.
                Lorem Ipsum has been the industry's standard dummy text ever since the 1500s,
                when an unknown printer took a galley of type and scrambled it to make a type specimen book.
                It has survived not only five centuries, but also the leap into electronic typesetting,
                remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset
                sheets containing Lorem Ipsum passages, and more recently with desktop publishing software
                like Aldus PageMaker including versions of Lorem Ipsum.
              </div>
              <!-- /.tab-pane -->
            </div>
            <!-- /.tab-content -->
          </div>
          <!-- nav-tabs-custom -->
        </div>
        <!-- /.col -->

        <div class="col-md-6">
          <!-- Custom Tabs (Pulled to the right) -->
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs pull-right">
              <li class="active"><a href="#tab_1-1" data-toggle="tab">Tab 1</a></li>
              <li><a href="#tab_2-2" data-toggle="tab">Tab 2</a></li>
              <li><a href="#tab_3-2" data-toggle="tab">Tab 3</a></li>
              <li class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                  Dropdown <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                  <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Action</a></li>
                  <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Another action</a></li>
                  <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Something else here</a></li>
                  <li role="presentation" class="divider"></li>
                  <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Separated link</a></li>
                </ul>
              </li>
              <li class="pull-left header"><i class="fa fa-th"></i> Custom Tabs</li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane active" id="tab_1-1">
                <b>How to use:</b>

                <p>Exactly like the original bootstrap tabs except you should use
                  the custom wrapper <code>.nav-tabs-custom</code> to achieve this style.</p>
                A wonderful serenity has taken possession of my entire soul,
                like these sweet mornings of spring which I enjoy with my whole heart.
                I am alone, and feel the charm of existence in this spot,
                which was created for the bliss of souls like mine. I am so happy,
                my dear friend, so absorbed in the exquisite sense of mere tranquil existence,
                that I neglect my talents. I should be incapable of drawing a single stroke
                at the present moment; and yet I feel that I never was a greater artist than now.
              </div>
              <!-- /.tab-pane -->
              <div class="tab-pane" id="tab_2-2">
                The European languages are members of the same family. Their separate existence is a myth.
                For science, music, sport, etc, Europe uses the same vocabulary. The languages only differ
                in their grammar, their pronunciation and their most common words. Everyone realizes why a
                new common language would be desirable: one could refuse to pay expensive translators. To
                achieve this, it would be necessary to have uniform grammar, pronunciation and more common
                words. If several languages coalesce, the grammar of the resulting language is more simple
                and regular than that of the individual languages.
              </div>
              <!-- /.tab-pane -->
              <div class="tab-pane" id="tab_3-2">
                Lorem Ipsum is simply dummy text of the printing and typesetting industry.
                Lorem Ipsum has been the industry's standard dummy text ever since the 1500s,
                when an unknown printer took a galley of type and scrambled it to make a type specimen book.
                It has survived not only five centuries, but also the leap into electronic typesetting,
                remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset
                sheets containing Lorem Ipsum passages, and more recently with desktop publishing software
                like Aldus PageMaker including versions of Lorem Ipsum.
              </div>
              <!-- /.tab-pane -->
            </div>
            <!-- /.tab-content -->
          </div>
          <!-- nav-tabs-custom -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
      <!-- END CUSTOM TABS -->
      <!-- START PROGRESS BARS -->
      <h2 class="page-header">Progress Bars</h2>

      <div class="row">
        <div class="col-md-6">
          <div class="box box-solid">
            <div class="box-header with-border">
              <h3 class="box-title">Progress Bars Different Sizes</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <p><code>.progress</code></p>

              <div class="progress">
                <div class="progress-bar progress-bar-primary progress-bar-striped" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 40%">
                  <span class="sr-only">40% Complete (success)</span>
                </div>
              </div>
              <p>Class: <code>.sm</code></p>

              <div class="progress progress-sm active">
                <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="width: 20%">
                  <span class="sr-only">20% Complete</span>
                </div>
              </div>
              <p>Class: <code>.xs</code></p>

              <div class="progress progress-xs">
                <div class="progress-bar progress-bar-warning progress-bar-striped" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 60%">
                  <span class="sr-only">60% Complete (warning)</span>
                </div>
              </div>
              <p>Class: <code>.xxs</code></p>

              <div class="progress progress-xxs">
                <div class="progress-bar progress-bar-danger progress-bar-striped" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 60%">
                  <span class="sr-only">60% Complete (warning)</span>
                </div>
              </div>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col (left) -->
        <div class="col-md-6">
          <div class="box box-solid">
            <div class="box-header with-border">
              <h3 class="box-title">Progress bars</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <div class="progress">
                <div class="progress-bar progress-bar-green" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 40%">
                  <span class="sr-only">40% Complete (success)</span>
                </div>
              </div>
              <div class="progress">
                <div class="progress-bar progress-bar-aqua" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="width: 20%">
                  <span class="sr-only">20% Complete</span>
                </div>
              </div>
              <div class="progress">
                <div class="progress-bar progress-bar-yellow" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 60%">
                  <span class="sr-only">60% Complete (warning)</span>
                </div>
              </div>
              <div class="progress">
                <div class="progress-bar progress-bar-red" role="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100" style="width: 80%">
                  <span class="sr-only">80% Complete</span>
                </div>
              </div>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col (right) -->
      </div>
      <!-- /.row -->
      <div class="row">
        <div class="col-md-6">
          <div class="box box-solid">
            <div class="box-header with-border">
              <h3 class="box-title">Vertical Progress Bars Different Sizes</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body text-center">
              <p>By adding the class <code>.vertical</code> and <code>.progress-sm</code>, <code>.progress-xs</code> or
                <code>.progress-xxs</code> we achieve:</p>

              <div class="progress vertical active">
                <div class="progress-bar progress-bar-primary progress-bar-striped" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="height: 40%">
                  <span class="sr-only">40%</span>
                </div>
              </div>
              <div class="progress vertical progress-sm">
                <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="height: 100%">
                  <span class="sr-only">100%</span>
                </div>
              </div>
              <div class="progress vertical progress-xs">
                <div class="progress-bar progress-bar-warning progress-bar-striped" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="height: 60%">
                  <span class="sr-only">60%</span>
                </div>
              </div>
              <div class="progress vertical progress-xxs">
                <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="height: 60%">
                  <span class="sr-only">60%</span>
                </div>
              </div>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col (left) -->
        <div class="col-md-6">
          <div class="box box-solid">
            <div class="box-header with-border">
              <h3 class="box-title">Vertical Progress bars</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body text-center">
              <p>By adding the class <code>.vertical</code> we achieve:</p>

              <div class="progress vertical">
                <div class="progress-bar progress-bar-green" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="height: 40%">
                  <span class="sr-only">40%</span>
                </div>
              </div>
              <div class="progress vertical">
                <div class="progress-bar progress-bar-aqua" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="height: 20%">
                  <span class="sr-only">20%</span>
                </div>
              </div>
              <div class="progress vertical">
                <div class="progress-bar progress-bar-yellow" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="height: 60%">
                  <span class="sr-only">60%</span>
                </div>
              </div>
              <div class="progress vertical">
                <div class="progress-bar progress-bar-red" role="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100" style="height: 80%">
                  <span class="sr-only">80%</span>
                </div>
              </div>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col (right) -->
      </div>
      <!-- /.row -->
      <!-- END PROGRESS BARS -->

      <!-- START ACCORDION & CAROUSEL-->
      <h2 class="page-header">Bootstrap Accordion & Carousel</h2>

      <div class="row">
        <div class="col-md-6">
          <div class="box box-solid">
            <div class="box-header with-border">
              <h3 class="box-title">Collapsible Accordion</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <div class="box-group" id="accordion">
                <!-- we are adding the .panel class so bootstrap.js collapse plugin detects it -->
                <div class="panel box box-primary">
                  <div class="box-header with-border">
                    <h4 class="box-title">
                      <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
                        Collapsible Group Item #1
                      </a>
                    </h4>
                  </div>
                  <div id="collapseOne" class="panel-collapse collapse in">
                    <div class="box-body">
                      Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3
                      wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum
                      eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla
                      assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred
                      nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer
                      farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus
                      labore sustainable VHS.
                    </div>
                  </div>
                </div>
                <div class="panel box box-danger">
                  <div class="box-header with-border">
                    <h4 class="box-title">
                      <a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">
                        Collapsible Group Danger
                      </a>
                    </h4>
                  </div>
                  <div id="collapseTwo" class="panel-collapse collapse">
                    <div class="box-body">
                      Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3
                      wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum
                      eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla
                      assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred
                      nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer
                      farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus
                      labore sustainable VHS.
                    </div>
                  </div>
                </div>
                <div class="panel box box-success">
                  <div class="box-header with-border">
                    <h4 class="box-title">
                      <a data-toggle="collapse" data-parent="#accordion" href="#collapseThree">
                        Collapsible Group Success
                      </a>
                    </h4>
                  </div>
                  <div id="collapseThree" class="panel-collapse collapse">
                    <div class="box-body">
                      Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3
                      wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum
                      eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla
                      assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred
                      nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer
                      farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus
                      labore sustainable VHS.
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->
        <div class="col-md-6">
          <div class="box box-solid">
            <div class="box-header with-border">
              <h3 class="box-title">Carousel</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
                <ol class="carousel-indicators">
                  <li data-target="#carousel-example-generic" data-slide-to="0" class="active"></li>
                  <li data-target="#carousel-example-generic" data-slide-to="1" class=""></li>
                  <li data-target="#carousel-example-generic" data-slide-to="2" class=""></li>
                </ol>
                <div class="carousel-inner">
                  <div class="item active">
                    <img src="http://placehold.it/900x500/39CCCC/ffffff&text=I+Love+Bootstrap" alt="First slide">

                    <div class="carousel-caption">
                      First Slide
                    </div>
                  </div>
                  <div class="item">
                    <img src="http://placehold.it/900x500/3c8dbc/ffffff&text=I+Love+Bootstrap" alt="Second slide">

                    <div class="carousel-caption">
                      Second Slide
                    </div>
                  </div>
                  <div class="item">
                    <img src="http://placehold.it/900x500/f39c12/ffffff&text=I+Love+Bootstrap" alt="Third slide">

                    <div class="carousel-caption">
                      Third Slide
                    </div>
                  </div>
                </div>
                <a class="left carousel-control" href="#carousel-example-generic" data-slide="prev">
                  <span class="fa fa-angle-left"></span>
                </a>
                <a class="right carousel-control" href="#carousel-example-generic" data-slide="next">
                  <span class="fa fa-angle-right"></span>
                </a>
              </div>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
      <!-- END ACCORDION & CAROUSEL-->

      <!-- START TYPOGRAPHY -->
      <h2 class="page-header">Typography</h2>

      <div class="row">
        <div class="col-md-6">
          <div class="box box-solid">
            <div class="box-header with-border">
              <i class="fa fa-text-width"></i>

              <h3 class="box-title">Headlines</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <h1>h1. Bootstrap heading</h1>

              <h2>h2. Bootstrap heading</h2>

              <h3>h3. Bootstrap heading</h3>
              <h4>h4. Bootstrap heading</h4>
              <h5>h5. Bootstrap heading</h5>
              <h6>h6. Bootstrap heading</h6>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- ./col -->
        <div class="col-md-6">
          <div class="box box-solid">
            <div class="box-header with-border">
              <i class="fa fa-text-width"></i>

              <h3 class="box-title">Text Emphasis</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <p class="lead">Lead to emphasize importance</p>

              <p class="text-green">Text green to emphasize success</p>

              <p class="text-aqua">Text aqua to emphasize info</p>

              <p class="text-light-blue">Text light blue to emphasize info (2)</p>

              <p class="text-red">Text red to emphasize danger</p>

              <p class="text-yellow">Text yellow to emphasize warning</p>

              <p class="text-muted">Text muted to emphasize general</p>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- ./col -->
      </div>
      <!-- /.row -->

      <div class="row">
        <div class="col-md-6">
          <div class="box box-solid">
            <div class="box-header with-border">
              <i class="fa fa-text-width"></i>

              <h3 class="box-title">Block Quotes</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <blockquote>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer posuere erat a ante.</p>
                <small>Someone famous in <cite title="Source Title">Source Title</cite></small>
              </blockquote>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- ./col -->
        <div class="col-md-6">
          <div class="box box-solid">
            <div class="box-header with-border">
              <i class="fa fa-text-width"></i>

              <h3 class="box-title">Block Quotes Pulled Right</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body clearfix">
              <blockquote class="pull-right">
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer posuere erat a ante.</p>
                <small>Someone famous in <cite title="Source Title">Source Title</cite></small>
              </blockquote>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- ./col -->
      </div>
      <!-- /.row -->

      <div class="row">
        <div class="col-md-4">
          <div class="box box-solid">
            <div class="box-header with-border">
              <i class="fa fa-text-width"></i>

              <h3 class="box-title">Unordered List</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <ul>
                <li>Lorem ipsum dolor sit amet</li>
                <li>Consectetur adipiscing elit</li>
                <li>Integer molestie lorem at massa</li>
                <li>Facilisis in pretium nisl aliquet</li>
                <li>Nulla volutpat aliquam velit
                  <ul>
                    <li>Phasellus iaculis neque</li>
                    <li>Purus sodales ultricies</li>
                    <li>Vestibulum laoreet porttitor sem</li>
                    <li>Ac tristique libero volutpat at</li>
                  </ul>
                </li>
                <li>Faucibus porta lacus fringilla vel</li>
                <li>Aenean sit amet erat nunc</li>
                <li>Eget porttitor lorem</li>
              </ul>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- ./col -->
        <div class="col-md-4">
          <div class="box box-solid">
            <div class="box-header with-border">
              <i class="fa fa-text-width"></i>

              <h3 class="box-title">Ordered Lists</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <ol>
                <li>Lorem ipsum dolor sit amet</li>
                <li>Consectetur adipiscing elit</li>
                <li>Integer molestie lorem at massa</li>
                <li>Facilisis in pretium nisl aliquet</li>
                <li>Nulla volutpat aliquam velit
                  <ol>
                    <li>Phasellus iaculis neque</li>
                    <li>Purus sodales ultricies</li>
                    <li>Vestibulum laoreet porttitor sem</li>
                    <li>Ac tristique libero volutpat at</li>
                  </ol>
                </li>
                <li>Faucibus porta lacus fringilla vel</li>
                <li>Aenean sit amet erat nunc</li>
                <li>Eget porttitor lorem</li>
              </ol>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- ./col -->
        <div class="col-md-4">
          <div class="box box-solid">
            <div class="box-header with-border">
              <i class="fa fa-text-width"></i>

              <h3 class="box-title">Unstyled List</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <ul class="list-unstyled">
                <li>Lorem ipsum dolor sit amet</li>
                <li>Consectetur adipiscing elit</li>
                <li>Integer molestie lorem at massa</li>
                <li>Facilisis in pretium nisl aliquet</li>
                <li>Nulla volutpat aliquam velit
                  <ul>
                    <li>Phasellus iaculis neque</li>
                    <li>Purus sodales ultricies</li>
                    <li>Vestibulum laoreet porttitor sem</li>
                    <li>Ac tristique libero volutpat at</li>
                  </ul>
                </li>
                <li>Faucibus porta lacus fringilla vel</li>
                <li>Aenean sit amet erat nunc</li>
                <li>Eget porttitor lorem</li>
              </ul>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- ./col -->
      </div>
      <!-- /.row -->

      <div class="row">
        <div class="col-md-6">
          <div class="box box-solid">
            <div class="box-header with-border">
              <i class="fa fa-text-width"></i>

              <h3 class="box-title">Description</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <dl>
                <dt>Description lists</dt>
                <dd>A description list is perfect for defining terms.</dd>
                <dt>Euismod</dt>
                <dd>Vestibulum id ligula porta felis euismod semper eget lacinia odio sem nec elit.</dd>
                <dd>Donec id elit non mi porta gravida at eget metus.</dd>
                <dt>Malesuada porta</dt>
                <dd>Etiam porta sem malesuada magna mollis euismod.</dd>
              </dl>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- ./col -->
        <div class="col-md-6">
          <div class="box box-solid">
            <div class="box-header with-border">
              <i class="fa fa-text-width"></i>

              <h3 class="box-title">Description Horizontal</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <dl class="dl-horizontal">
                <dt>Description lists</dt>
                <dd>A description list is perfect for defining terms.</dd>
                <dt>Euismod</dt>
                <dd>Vestibulum id ligula porta felis euismod semper eget lacinia odio sem nec elit.</dd>
                <dd>Donec id elit non mi porta gravida at eget metus.</dd>
                <dt>Malesuada porta</dt>
                <dd>Etiam porta sem malesuada magna mollis euismod.</dd>
                <dt>Felis euismod semper eget lacinia</dt>
                <dd>Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo
                  sit amet risus.
                </dd>
              </dl>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- ./col -->
      </div>
      <!-- /.row -->
      <!-- END TYPOGRAPHY -->

    </section>
    <!-- /.content -->
<!-- Buttons -->
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Buttons
        <small>Control panel</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">UI</a></li>
        <li class="active">Buttons</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content">

      <div class="row">
        <div class="col-md-12">
          <div class="box box-primary">
            <div class="box-header">
              <i class="fa fa-edit"></i>

              <h3 class="box-title">Buttons</h3>
            </div>
            <div class="box-body pad table-responsive">
              <p>Various types of buttons. Using the base class <code>.btn</code></p>
              <table class="table table-bordered text-center">
                <tr>
                  <th>Normal</th>
                  <th>Large <code>.btn-lg</code></th>
                  <th>Small <code>.btn-sm</code></th>
                  <th>X-Small <code>.btn-xs</code></th>
                  <th>Flat <code>.btn-flat</code></th>
                  <th>Disabled <code>.disabled</code></th>
                </tr>
                <tr>
                  <td>
                    <button type="button" class="btn btn-block btn-default">Default</button>
                  </td>
                  <td>
                    <button type="button" class="btn btn-block btn-default btn-lg">Default</button>
                  </td>
                  <td>
                    <button type="button" class="btn btn-block btn-default btn-sm">Default</button>
                  </td>
                  <td>
                    <button type="button" class="btn btn-block btn-default btn-xs">Default</button>
                  </td>
                  <td>
                    <button type="button" class="btn btn-block btn-default btn-flat">Default</button>
                  </td>
                  <td>
                    <button type="button" class="btn btn-block btn-default disabled">Default</button>
                  </td>
                </tr>
                <tr>
                  <td>
                    <button type="button" class="btn btn-block btn-primary">Primary</button>
                  </td>
                  <td>
                    <button type="button" class="btn btn-block btn-primary btn-lg">Primary</button>
                  </td>
                  <td>
                    <button type="button" class="btn btn-block btn-primary btn-sm">Primary</button>
                  </td>
                  <td>
                    <button type="button" class="btn btn-block btn-primary btn-xs">Primary</button>
                  </td>
                  <td>
                    <button type="button" class="btn btn-block btn-primary btn-flat">Primary</button>
                  </td>
                  <td>
                    <button type="button" class="btn btn-block btn-primary disabled">Primary</button>
                  </td>
                </tr>
                <tr>
                  <td>
                    <button type="button" class="btn btn-block btn-success">Success</button>
                  </td>
                  <td>
                    <button type="button" class="btn btn-block btn-success btn-lg">Success</button>
                  </td>
                  <td>
                    <button type="button" class="btn btn-block btn-success btn-sm">Success</button>
                  </td>
                  <td>
                    <button type="button" class="btn btn-block btn-success btn-xs">Success</button>
                  </td>
                  <td>
                    <button type="button" class="btn btn-block btn-success btn-flat">Success</button>
                  </td>
                  <td>
                    <button type="button" class="btn btn-block btn-success disabled">Success</button>
                  </td>
                </tr>
                <tr>
                  <td>
                    <button type="button" class="btn btn-block btn-info">Info</button>
                  </td>
                  <td>
                    <button type="button" class="btn btn-block btn-info btn-lg">Info</button>
                  </td>
                  <td>
                    <button type="button" class="btn btn-block btn-info btn-sm">Info</button>
                  </td>
                  <td>
                    <button type="button" class="btn btn-block btn-info btn-xs">Info</button>
                  </td>
                  <td>
                    <button type="button" class="btn btn-block btn-info btn-flat">Info</button>
                  </td>
                  <td>
                    <button type="button" class="btn btn-block btn-info disabled">Info</button>
                  </td>
                </tr>
                <tr>
                  <td>
                    <button type="button" class="btn btn-block btn-danger">Danger</button>
                  </td>
                  <td>
                    <button type="button" class="btn btn-block btn-danger btn-lg">Danger</button>
                  </td>
                  <td>
                    <button type="button" class="btn btn-block btn-danger btn-sm">Danger</button>
                  </td>
                  <td>
                    <button type="button" class="btn btn-block btn-danger btn-xs">Danger</button>
                  </td>
                  <td>
                    <button type="button" class="btn btn-block btn-danger btn-flat">Danger</button>
                  </td>
                  <td>
                    <button type="button" class="btn btn-block btn-danger disabled">Danger</button>
                  </td>
                </tr>
                <tr>
                  <td>
                    <button type="button" class="btn btn-block btn-warning">Warning</button>
                  </td>
                  <td>
                    <button type="button" class="btn btn-block btn-warning btn-lg">Warning</button>
                  </td>
                  <td>
                    <button type="button" class="btn btn-block btn-warning btn-sm">Warning</button>
                  </td>
                  <td>
                    <button type="button" class="btn btn-block btn-warning btn-xs">Warning</button>
                  </td>
                  <td>
                    <button type="button" class="btn btn-block btn-warning btn-flat">Warning</button>
                  </td>
                  <td>
                    <button type="button" class="btn btn-block btn-warning disabled">Warning</button>
                  </td>
                </tr>
              </table>
            </div>
            <!-- /.box -->
          </div>
        </div>
        <!-- /.col -->
      </div>
      <!-- ./row -->
      <div class="row">
        <div class="col-md-6">
          <!-- Block buttons -->
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Block Buttons</h3>
            </div>
            <div class="box-body">
              <button type="button" class="btn btn-default btn-block">.btn-block</button>
              <button type="button" class="btn btn-default btn-block btn-flat">.btn-block .btn-flat</button>
              <button type="button" class="btn btn-default btn-block btn-sm">.btn-block .btn-sm</button>
            </div>
          </div>
          <!-- /.box -->

          <!-- Horizontal grouping -->
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Horizontal Button Group</h3>
            </div>
            <div class="box-body table-responsive pad">
              <p>
                Horizontal button groups are easy to create with bootstrap. Just add your buttons
                inside <code>&lt;div class="btn-group"&gt;&lt;/div&gt;</code>
              </p>

              <table class="table table-bordered">
                <tr>
                  <th>Button</th>
                  <th>Icons</th>
                  <th>Flat</th>
                  <th>Dropdown</th>
                </tr>
                <!-- Default -->
                <tr>
                  <td>
                    <div class="btn-group">
                      <button type="button" class="btn btn-default">Left</button>
                      <button type="button" class="btn btn-default">Middle</button>
                      <button type="button" class="btn btn-default">Right</button>
                    </div>
                  </td>
                  <td>
                    <div class="btn-group">
                      <button type="button" class="btn btn-default"><i class="fa fa-align-left"></i></button>
                      <button type="button" class="btn btn-default"><i class="fa fa-align-center"></i></button>
                      <button type="button" class="btn btn-default"><i class="fa fa-align-right"></i></button>
                    </div>
                  </td>
                  <td>
                    <div class="btn-group">
                      <button type="button" class="btn btn-default btn-flat"><i class="fa fa-align-left"></i></button>
                      <button type="button" class="btn btn-default btn-flat"><i class="fa fa-align-center"></i></button>
                      <button type="button" class="btn btn-default btn-flat"><i class="fa fa-align-right"></i></button>
                    </div>
                  </td>
                  <td>
                    <div class="btn-group">
                      <button type="button" class="btn btn-default">1</button>
                      <button type="button" class="btn btn-default">2</button>

                      <div class="btn-group">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                          <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                          <li><a href="#">Dropdown link</a></li>
                          <li><a href="#">Dropdown link</a></li>
                        </ul>
                      </div>
                    </div>
                  </td>
                </tr>
                <!-- ./default -->
                <!-- Info -->
                <tr>
                  <td>
                    <div class="btn-group">
                      <button type="button" class="btn btn-info">Left</button>
                      <button type="button" class="btn btn-info">Middle</button>
                      <button type="button" class="btn btn-info">Right</button>
                    </div>
                  </td>
                  <td>
                    <div class="btn-group">
                      <button type="button" class="btn btn-info"><i class="fa fa-align-left"></i></button>
                      <button type="button" class="btn btn-info"><i class="fa fa-align-center"></i></button>
                      <button type="button" class="btn btn-info"><i class="fa fa-align-right"></i></button>
                    </div>
                  </td>
                  <td>
                    <div class="btn-group">
                      <button type="button" class="btn btn-info btn-flat"><i class="fa fa-align-left"></i></button>
                      <button type="button" class="btn btn-info btn-flat"><i class="fa fa-align-center"></i></button>
                      <button type="button" class="btn btn-info btn-flat"><i class="fa fa-align-right"></i></button>
                    </div>
                  </td>
                  <td>
                    <div class="btn-group">
                      <button type="button" class="btn btn-info">1</button>
                      <button type="button" class="btn btn-info">2</button>

                      <div class="btn-group">
                        <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                          <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                          <li><a href="#">Dropdown link</a></li>
                          <li><a href="#">Dropdown link</a></li>
                        </ul>
                      </div>
                    </div>
                  </td>
                </tr>
                <!-- /. info -->
                <!-- /.danger -->
                <tr>
                  <td>
                    <div class="btn-group">
                      <button type="button" class="btn btn-danger">Left</button>
                      <button type="button" class="btn btn-danger">Middle</button>
                      <button type="button" class="btn btn-danger">Right</button>
                    </div>
                  </td>
                  <td>
                    <div class="btn-group">
                      <button type="button" class="btn btn-danger"><i class="fa fa-align-left"></i></button>
                      <button type="button" class="btn btn-danger"><i class="fa fa-align-center"></i></button>
                      <button type="button" class="btn btn-danger"><i class="fa fa-align-right"></i></button>
                    </div>
                  </td>
                  <td>
                    <div class="btn-group">
                      <button type="button" class="btn btn-danger btn-flat"><i class="fa fa-align-left"></i></button>
                      <button type="button" class="btn btn-danger btn-flat"><i class="fa fa-align-center"></i></button>
                      <button type="button" class="btn btn-danger btn-flat"><i class="fa fa-align-right"></i></button>
                    </div>
                  </td>
                  <td>
                    <div class="btn-group">
                      <button type="button" class="btn btn-danger">1</button>
                      <button type="button" class="btn btn-danger">2</button>

                      <div class="btn-group">
                        <button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown">
                          <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                          <li><a href="#">Dropdown link</a></li>
                          <li><a href="#">Dropdown link</a></li>
                        </ul>
                      </div>
                    </div>
                  </td>
                </tr>
                <!-- /.danger -->
                <!-- warning -->
                <tr>
                  <td>
                    <div class="btn-group">
                      <button type="button" class="btn btn-warning">Left</button>
                      <button type="button" class="btn btn-warning">Middle</button>
                      <button type="button" class="btn btn-warning">Right</button>
                    </div>
                  </td>
                  <td>
                    <div class="btn-group">
                      <button type="button" class="btn btn-warning"><i class="fa fa-align-left"></i></button>
                      <button type="button" class="btn btn-warning"><i class="fa fa-align-center"></i></button>
                      <button type="button" class="btn btn-warning"><i class="fa fa-align-right"></i></button>
                    </div>
                  </td>
                  <td>
                    <div class="btn-group">
                      <button type="button" class="btn btn-warning btn-flat"><i class="fa fa-align-left"></i></button>
                      <button type="button" class="btn btn-warning btn-flat"><i class="fa fa-align-center"></i></button>
                      <button type="button" class="btn btn-warning btn-flat"><i class="fa fa-align-right"></i></button>
                    </div>
                  </td>
                  <td>
                    <div class="btn-group">
                      <button type="button" class="btn btn-warning">1</button>
                      <button type="button" class="btn btn-warning">2</button>

                      <div class="btn-group">
                        <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown">
                          <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                          <li><a href="#">Dropdown link</a></li>
                          <li><a href="#">Dropdown link</a></li>
                        </ul>
                      </div>
                    </div>
                  </td>
                </tr>
                <!-- /.warning -->
                <!-- success -->
                <tr>
                  <td>
                    <div class="btn-group">
                      <button type="button" class="btn btn-success">Left</button>
                      <button type="button" class="btn btn-success">Middle</button>
                      <button type="button" class="btn btn-success">Right</button>
                    </div>
                  </td>
                  <td>
                    <div class="btn-group">
                      <button type="button" class="btn btn-success"><i class="fa fa-align-left"></i></button>
                      <button type="button" class="btn btn-success"><i class="fa fa-align-center"></i></button>
                      <button type="button" class="btn btn-success"><i class="fa fa-align-right"></i></button>
                    </div>
                  </td>
                  <td>
                    <div class="btn-group">
                      <button type="button" class="btn btn-success btn-flat"><i class="fa fa-align-left"></i></button>
                      <button type="button" class="btn btn-success btn-flat"><i class="fa fa-align-center"></i></button>
                      <button type="button" class="btn btn-success btn-flat"><i class="fa fa-align-right"></i></button>
                    </div>
                  </td>
                  <td>
                    <div class="btn-group">
                      <button type="button" class="btn btn-success">1</button>
                      <button type="button" class="btn btn-success">2</button>

                      <div class="btn-group">
                        <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
                          <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                          <li><a href="#">Dropdown link</a></li>
                          <li><a href="#">Dropdown link</a></li>
                        </ul>
                      </div>
                    </div>
                  </td>
                </tr>
                <!-- /.success -->
              </table>
            </div>
          </div>
          <!-- /.box -->

          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Button Addon</h3>
            </div>
            <div class="box-body">
              <p>With dropdown</p>

              <div class="input-group margin">
                <div class="input-group-btn">
                  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Action
                    <span class="fa fa-caret-down"></span></button>
                  <ul class="dropdown-menu">
                    <li><a href="#">Action</a></li>
                    <li><a href="#">Another action</a></li>
                    <li><a href="#">Something else here</a></li>
                    <li class="divider"></li>
                    <li><a href="#">Separated link</a></li>
                  </ul>
                </div>
                <!-- /btn-group -->
                <input type="text" class="form-control">
              </div>
              <!-- /input-group -->
              <p>Normal</p>

              <div class="input-group margin">
                <div class="input-group-btn">
                  <button type="button" class="btn btn-danger">Action</button>
                </div>
                <!-- /btn-group -->
                <input type="text" class="form-control">
              </div>
              <!-- /input-group -->
              <p>Flat</p>

              <div class="input-group margin">
                <input type="text" class="form-control">
                    <span class="input-group-btn">
                      <button type="button" class="btn btn-info btn-flat">Go!</button>
                    </span>
              </div>
              <!-- /input-group -->
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
          <!-- split buttons box -->
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Split buttons</h3>
            </div>
            <div class="box-body">
              <!-- Split button -->
              <p>Normal split buttons:</p>

              <div class="margin">
                <div class="btn-group">
                  <button type="button" class="btn btn-default">Action</button>
                  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                  </button>
                  <ul class="dropdown-menu" role="menu">
                    <li><a href="#">Action</a></li>
                    <li><a href="#">Another action</a></li>
                    <li><a href="#">Something else here</a></li>
                    <li class="divider"></li>
                    <li><a href="#">Separated link</a></li>
                  </ul>
                </div>
                <div class="btn-group">
                  <button type="button" class="btn btn-info">Action</button>
                  <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                  </button>
                  <ul class="dropdown-menu" role="menu">
                    <li><a href="#">Action</a></li>
                    <li><a href="#">Another action</a></li>
                    <li><a href="#">Something else here</a></li>
                    <li class="divider"></li>
                    <li><a href="#">Separated link</a></li>
                  </ul>
                </div>
                <div class="btn-group">
                  <button type="button" class="btn btn-danger">Action</button>
                  <button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                  </button>
                  <ul class="dropdown-menu" role="menu">
                    <li><a href="#">Action</a></li>
                    <li><a href="#">Another action</a></li>
                    <li><a href="#">Something else here</a></li>
                    <li class="divider"></li>
                    <li><a href="#">Separated link</a></li>
                  </ul>
                </div>
                <div class="btn-group">
                  <button type="button" class="btn btn-success">Action</button>
                  <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                  </button>
                  <ul class="dropdown-menu" role="menu">
                    <li><a href="#">Action</a></li>
                    <li><a href="#">Another action</a></li>
                    <li><a href="#">Something else here</a></li>
                    <li class="divider"></li>
                    <li><a href="#">Separated link</a></li>
                  </ul>
                </div>
                <div class="btn-group">
                  <button type="button" class="btn btn-warning">Action</button>
                  <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                  </button>
                  <ul class="dropdown-menu" role="menu">
                    <li><a href="#">Action</a></li>
                    <li><a href="#">Another action</a></li>
                    <li><a href="#">Something else here</a></li>
                    <li class="divider"></li>
                    <li><a href="#">Separated link</a></li>
                  </ul>
                </div>
              </div>
              <!-- flat split buttons -->
              <p>Flat split buttons:</p>

              <div class="margin">
                <div class="btn-group">
                  <button type="button" class="btn btn-default btn-flat">Action</button>
                  <button type="button" class="btn btn-default btn-flat dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                  </button>
                  <ul class="dropdown-menu" role="menu">
                    <li><a href="#">Action</a></li>
                    <li><a href="#">Another action</a></li>
                    <li><a href="#">Something else here</a></li>
                    <li class="divider"></li>
                    <li><a href="#">Separated link</a></li>
                  </ul>
                </div>
                <div class="btn-group">
                  <button type="button" class="btn btn-info btn-flat">Action</button>
                  <button type="button" class="btn btn-info btn-flat dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                  </button>
                  <ul class="dropdown-menu" role="menu">
                    <li><a href="#">Action</a></li>
                    <li><a href="#">Another action</a></li>
                    <li><a href="#">Something else here</a></li>
                    <li class="divider"></li>
                    <li><a href="#">Separated link</a></li>
                  </ul>
                </div>
                <div class="btn-group">
                  <button type="button" class="btn btn-danger btn-flat">Action</button>
                  <button type="button" class="btn btn-danger btn-flat dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                  </button>
                  <ul class="dropdown-menu" role="menu">
                    <li><a href="#">Action</a></li>
                    <li><a href="#">Another action</a></li>
                    <li><a href="#">Something else here</a></li>
                    <li class="divider"></li>
                    <li><a href="#">Separated link</a></li>
                  </ul>
                </div>
                <div class="btn-group">
                  <button type="button" class="btn btn-success btn-flat">Action</button>
                  <button type="button" class="btn btn-success btn-flat dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                  </button>
                  <ul class="dropdown-menu" role="menu">
                    <li><a href="#">Action</a></li>
                    <li><a href="#">Another action</a></li>
                    <li><a href="#">Something else here</a></li>
                    <li class="divider"></li>
                    <li><a href="#">Separated link</a></li>
                  </ul>
                </div>
                <div class="btn-group">
                  <button type="button" class="btn btn-warning btn-flat">Action</button>
                  <button type="button" class="btn btn-warning btn-flat dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                  </button>
                  <ul class="dropdown-menu" role="menu">
                    <li><a href="#">Action</a></li>
                    <li><a href="#">Another action</a></li>
                    <li><a href="#">Something else here</a></li>
                    <li class="divider"></li>
                    <li><a href="#">Separated link</a></li>
                  </ul>
                </div>
              </div>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- end split buttons box -->

          <!-- social buttons -->
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Social Buttons (By <a href="https://github.com/lipis/bootstrap-social">Lipis</a>)
              </h3>
            </div>
            <div class="box-body">
              <a class="btn btn-block btn-social btn-bitbucket">
                <i class="fa fa-bitbucket"></i> Sign in with Bitbucket
              </a>
              <a class="btn btn-block btn-social btn-dropbox">
                <i class="fa fa-dropbox"></i> Sign in with Dropbox
              </a>
              <a class="btn btn-block btn-social btn-facebook">
                <i class="fa fa-facebook"></i> Sign in with Facebook
              </a>
              <a class="btn btn-block btn-social btn-flickr">
                <i class="fa fa-flickr"></i> Sign in with Flickr
              </a>
              <a class="btn btn-block btn-social btn-foursquare">
                <i class="fa fa-foursquare"></i> Sign in with Foursquare
              </a>
              <a class="btn btn-block btn-social btn-github">
                <i class="fa fa-github"></i> Sign in with GitHub
              </a>
              <a class="btn btn-block btn-social btn-google">
                <i class="fa fa-google-plus"></i> Sign in with Google
              </a>
              <a class="btn btn-block btn-social btn-instagram">
                <i class="fa fa-instagram"></i> Sign in with Instagram
              </a>
              <a class="btn btn-block btn-social btn-linkedin">
                <i class="fa fa-linkedin"></i> Sign in with LinkedIn
              </a>
              <a class="btn btn-block btn-social btn-tumblr">
                <i class="fa fa-tumblr"></i> Sign in with Tumblr
              </a>
              <a class="btn btn-block btn-social btn-twitter">
                <i class="fa fa-twitter"></i> Sign in with Twitter
              </a>
              <a class="btn btn-block btn-social btn-vk">
                <i class="fa fa-vk"></i> Sign in with VK
              </a>
              <br>

              <div class="text-center">
                <a class="btn btn-social-icon btn-bitbucket"><i class="fa fa-bitbucket"></i></a>
                <a class="btn btn-social-icon btn-dropbox"><i class="fa fa-dropbox"></i></a>
                <a class="btn btn-social-icon btn-facebook"><i class="fa fa-facebook"></i></a>
                <a class="btn btn-social-icon btn-flickr"><i class="fa fa-flickr"></i></a>
                <a class="btn btn-social-icon btn-foursquare"><i class="fa fa-foursquare"></i></a>
                <a class="btn btn-social-icon btn-github"><i class="fa fa-github"></i></a>
                <a class="btn btn-social-icon btn-google"><i class="fa fa-google-plus"></i></a>
                <a class="btn btn-social-icon btn-instagram"><i class="fa fa-instagram"></i></a>
                <a class="btn btn-social-icon btn-linkedin"><i class="fa fa-linkedin"></i></a>
                <a class="btn btn-social-icon btn-tumblr"><i class="fa fa-tumblr"></i></a>
                <a class="btn btn-social-icon btn-twitter"><i class="fa fa-twitter"></i></a>
                <a class="btn btn-social-icon btn-vk"><i class="fa fa-vk"></i></a>
              </div>
            </div>
          </div>
          <!-- /.box -->

        </div>
        <!-- /.col -->
        <div class="col-md-6">
          <!-- Application buttons -->
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Application Buttons</h3>
            </div>
            <div class="box-body">
              <p>Add the classes <code>.btn.btn-app</code> to an <code>&lt;a></code> tag to achieve the following:</p>
              <a class="btn btn-app">
                <i class="fa fa-edit"></i> Edit
              </a>
              <a class="btn btn-app">
                <i class="fa fa-play"></i> Play
              </a>
              <a class="btn btn-app">
                <i class="fa fa-repeat"></i> Repeat
              </a>
              <a class="btn btn-app">
                <i class="fa fa-pause"></i> Pause
              </a>
              <a class="btn btn-app">
                <i class="fa fa-save"></i> Save
              </a>
              <a class="btn btn-app">
                <span class="badge bg-yellow">3</span>
                <i class="fa fa-bullhorn"></i> Notifications
              </a>
              <a class="btn btn-app">
                <span class="badge bg-green">300</span>
                <i class="fa fa-barcode"></i> Products
              </a>
              <a class="btn btn-app">
                <span class="badge bg-purple">891</span>
                <i class="fa fa-users"></i> Users
              </a>
              <a class="btn btn-app">
                <span class="badge bg-teal">67</span>
                <i class="fa fa-inbox"></i> Orders
              </a>
              <a class="btn btn-app">
                <span class="badge bg-aqua">12</span>
                <i class="fa fa-envelope"></i> Inbox
              </a>
              <a class="btn btn-app">
                <span class="badge bg-red">531</span>
                <i class="fa fa-heart-o"></i> Likes
              </a>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
          <!-- Various colors -->
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Different colors</h3>
            </div>
            <div class="box-body">
              <p>Mix and match with various background colors. Use base class <code>.btn</code> and add any available
                <code>.bg-*</code> class</p>
              <!-- You may notice a .margin class added
              here but that's only to make the content
              display correctly without having to use a table-->
              <p>
                <button type="button" class="btn bg-maroon btn-flat margin">.btn.bg-maroon.btn-flat</button>
                <button type="button" class="btn bg-purple btn-flat margin">.btn.bg-purple.btn-flat</button>
                <button type="button" class="btn bg-navy btn-flat margin">.btn.bg-navy.btn-flat</button>
                <button type="button" class="btn bg-orange btn-flat margin">.btn.bg-orange.btn-flat</button>
                <button type="button" class="btn bg-olive btn-flat margin">.btn.bg-olive.btn-flat</button>
              </p>

              <p>
                <button type="button" class="btn bg-maroon margin">.btn.bg-maroon</button>
                <button type="button" class="btn bg-purple margin">.btn.bg-purple</button>
                <button type="button" class="btn bg-navy margin">.btn.bg-navy</button>
                <button type="button" class="btn bg-orange margin">.btn.bg-orange</button>
                <button type="button" class="btn bg-olive margin">.btn.bg-olive</button>
              </p>
            </div>
          </div>
          <!-- /.box -->

          <!-- Vertical grouping -->
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Vertical Button Group</h3>
            </div>
            <div class="box-body table-responsive pad">

              <p>
                Vertical button groups are easy to create with bootstrap. Just add your buttons
                inside <code>&lt;div class="btn-group-vertical"&gt;&lt;/div&gt;</code>
              </p>

              <table class="table table-bordered">
                <tr>
                  <th>Button</th>
                  <th>Icons</th>
                  <th>Flat</th>
                  <th>Dropdown</th>
                </tr>
                <!-- Default -->
                <tr>
                  <td>
                    <div class="btn-group-vertical">
                      <button type="button" class="btn btn-default">Top</button>
                      <button type="button" class="btn btn-default">Middle</button>
                      <button type="button" class="btn btn-default">Bottom</button>
                    </div>
                  </td>
                  <td>
                    <div class="btn-group-vertical">
                      <button type="button" class="btn btn-default"><i class="fa fa-align-left"></i></button>
                      <button type="button" class="btn btn-default"><i class="fa fa-align-center"></i></button>
                      <button type="button" class="btn btn-default"><i class="fa fa-align-right"></i></button>
                    </div>
                  </td>
                  <td>
                    <div class="btn-group-vertical">
                      <button type="button" class="btn btn-default btn-flat"><i class="fa fa-align-left"></i></button>
                      <button type="button" class="btn btn-default btn-flat"><i class="fa fa-align-center"></i></button>
                      <button type="button" class="btn btn-default btn-flat"><i class="fa fa-align-right"></i></button>
                    </div>
                  </td>
                  <td>
                    <div class="btn-group-vertical">
                      <button type="button" class="btn btn-default">1</button>
                      <button type="button" class="btn btn-default">2</button>

                      <div class="btn-group">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                          <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                          <li><a href="#">Dropdown link</a></li>
                          <li><a href="#">Dropdown link</a></li>
                        </ul>
                      </div>
                    </div>
                  </td>
                </tr>
                <!-- ./default -->
                <!-- Info -->
                <tr>
                  <td>
                    <div class="btn-group-vertical">
                      <button type="button" class="btn btn-info">Top</button>
                      <button type="button" class="btn btn-info">Middle</button>
                      <button type="button" class="btn btn-info">Bottom</button>
                    </div>
                  </td>
                  <td>
                    <div class="btn-group-vertical">
                      <button type="button" class="btn btn-info"><i class="fa fa-align-left"></i></button>
                      <button type="button" class="btn btn-info"><i class="fa fa-align-center"></i></button>
                      <button type="button" class="btn btn-info"><i class="fa fa-align-right"></i></button>
                    </div>
                  </td>
                  <td>
                    <div class="btn-group-vertical">
                      <button type="button" class="btn btn-info btn-flat"><i class="fa fa-align-left"></i></button>
                      <button type="button" class="btn btn-info btn-flat"><i class="fa fa-align-center"></i></button>
                      <button type="button" class="btn btn-info btn-flat"><i class="fa fa-align-right"></i></button>
                    </div>
                  </td>
                  <td>
                    <div class="btn-group-vertical">
                      <button type="button" class="btn btn-info">1</button>
                      <button type="button" class="btn btn-info">2</button>

                      <div class="btn-group">
                        <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                          <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                          <li><a href="#">Dropdown link</a></li>
                          <li><a href="#">Dropdown link</a></li>
                        </ul>
                      </div>
                    </div>
                  </td>
                </tr>
                <!-- /. info -->
                <!-- /.danger -->
                <tr>
                  <td>
                    <div class="btn-group-vertical">
                      <button type="button" class="btn btn-danger">Top</button>
                      <button type="button" class="btn btn-danger">Middle</button>
                      <button type="button" class="btn btn-danger">Bottom</button>
                    </div>
                  </td>
                  <td>
                    <div class="btn-group-vertical">
                      <button type="button" class="btn btn-danger"><i class="fa fa-align-left"></i></button>
                      <button type="button" class="btn btn-danger"><i class="fa fa-align-center"></i></button>
                      <button type="button" class="btn btn-danger"><i class="fa fa-align-right"></i></button>
                    </div>
                  </td>
                  <td>
                    <div class="btn-group-vertical">
                      <button type="button" class="btn btn-danger btn-flat"><i class="fa fa-align-left"></i></button>
                      <button type="button" class="btn btn-danger btn-flat"><i class="fa fa-align-center"></i></button>
                      <button type="button" class="btn btn-danger btn-flat"><i class="fa fa-align-right"></i></button>
                    </div>
                  </td>
                  <td>
                    <div class="btn-group-vertical">
                      <button type="button" class="btn btn-danger">1</button>
                      <button type="button" class="btn btn-danger">2</button>

                      <div class="btn-group">
                        <button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown">
                          <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                          <li><a href="#">Dropdown link</a></li>
                          <li><a href="#">Dropdown link</a></li>
                        </ul>
                      </div>
                    </div>
                  </td>
                </tr>
                <!-- /.danger -->
                <!-- warning -->
                <tr>
                  <td>
                    <div class="btn-group-vertical">
                      <button type="button" class="btn btn-warning">Top</button>
                      <button type="button" class="btn btn-warning">Middle</button>
                      <button type="button" class="btn btn-warning">Bottom</button>
                    </div>
                  </td>
                  <td>
                    <div class="btn-group-vertical">
                      <button type="button" class="btn btn-warning"><i class="fa fa-align-left"></i></button>
                      <button type="button" class="btn btn-warning"><i class="fa fa-align-center"></i></button>
                      <button type="button" class="btn btn-warning"><i class="fa fa-align-right"></i></button>
                    </div>
                  </td>
                  <td>
                    <div class="btn-group-vertical">
                      <button type="button" class="btn btn-warning btn-flat"><i class="fa fa-align-left"></i></button>
                      <button type="button" class="btn btn-warning btn-flat"><i class="fa fa-align-center"></i></button>
                      <button type="button" class="btn btn-warning btn-flat"><i class="fa fa-align-right"></i></button>
                    </div>
                  </td>
                  <td>
                    <div class="btn-group-vertical">
                      <button type="button" class="btn btn-warning">1</button>
                      <button type="button" class="btn btn-warning">2</button>

                      <div class="btn-group">
                        <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown">
                          <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                          <li><a href="#">Dropdown link</a></li>
                          <li><a href="#">Dropdown link</a></li>
                        </ul>
                      </div>
                    </div>
                  </td>
                </tr>
                <!-- /.warning -->
                <!-- success -->
                <tr>
                  <td>
                    <div class="btn-group-vertical">
                      <button type="button" class="btn btn-success">Top</button>
                      <button type="button" class="btn btn-success">Middle</button>
                      <button type="button" class="btn btn-success">Bottom</button>
                    </div>
                  </td>
                  <td>
                    <div class="btn-group-vertical">
                      <button type="button" class="btn btn-success"><i class="fa fa-align-left"></i></button>
                      <button type="button" class="btn btn-success"><i class="fa fa-align-center"></i></button>
                      <button type="button" class="btn btn-success"><i class="fa fa-align-right"></i></button>
                    </div>
                  </td>
                  <td>
                    <div class="btn-group-vertical">
                      <button type="button" class="btn btn-success btn-flat"><i class="fa fa-align-left"></i></button>
                      <button type="button" class="btn btn-success btn-flat"><i class="fa fa-align-center"></i></button>
                      <button type="button" class="btn btn-success btn-flat"><i class="fa fa-align-right"></i></button>
                    </div>
                  </td>
                  <td>
                    <div class="btn-group-vertical">
                      <button type="button" class="btn btn-success">1</button>
                      <button type="button" class="btn btn-success">2</button>

                      <div class="btn-group">
                        <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
                          <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                          <li><a href="#">Dropdown link</a></li>
                          <li><a href="#">Dropdown link</a></li>
                        </ul>
                      </div>
                    </div>
                  </td>
                </tr>
                <!-- /.success -->
              </table>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /. row -->
    </section>
    <!-- /.content -->
<!-- Sliders -->
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Sliders
        <small>range sliders</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">UI</a></li>
        <li class="active">Sliders</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="box box-primary">
            <div class="box-header">
              <h3 class="box-title">Bootstrap Slider</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <div class="row margin">
                <div class="col-sm-6">
                  <input type="text" value="" class="slider form-control" data-slider-min="-200" data-slider-max="200"
                         data-slider-step="5" data-slider-value="[-100,100]" data-slider-orientation="horizontal"
                         data-slider-selection="before" data-slider-tooltip="show" data-slider-id="red">

                  <p>data-slider-id="red"</p>
                  <input type="text" value="" class="slider form-control" data-slider-min="-200" data-slider-max="200"
                         data-slider-step="5" data-slider-value="[-100,100]" data-slider-orientation="horizontal"
                         data-slider-selection="before" data-slider-tooltip="show" data-slider-id="blue">

                  <p>data-slider-id="blue"</p>
                  <input type="text" value="" class="slider form-control" data-slider-min="-200" data-slider-max="200"
                         data-slider-step="5" data-slider-value="[-100,100]" data-slider-orientation="horizontal"
                         data-slider-selection="before" data-slider-tooltip="show" data-slider-id="green">

                  <p>data-slider-id="green"</p>
                  <input type="text" value="" class="slider form-control" data-slider-min="-200" data-slider-max="200"
                         data-slider-step="5" data-slider-value="[-100,100]" data-slider-orientation="horizontal"
                         data-slider-selection="before" data-slider-tooltip="show" data-slider-id="yellow">

                  <p>data-slider-id="yellow"</p>
                  <input type="text" value="" class="slider form-control" data-slider-min="-200" data-slider-max="200"
                         data-slider-step="5" data-slider-value="[-100,100]" data-slider-orientation="horizontal"
                         data-slider-selection="before" data-slider-tooltip="show" data-slider-id="aqua">

                  <p>data-slider-id="aqua"</p>
                  <input type="text" value="" class="slider form-control" data-slider-min="-200" data-slider-max="200"
                         data-slider-step="5" data-slider-value="[-100,100]" data-slider-orientation="horizontal"
                         data-slider-selection="before" data-slider-tooltip="show" data-slider-id="purple">

                  <p style="margin-top: 10px">data-slider-id="purple"</p>
                </div>
                <div class="col-sm-6 text-center">
                  <input type="text" value="" class="slider form-control" data-slider-min="-200" data-slider-max="200"
                         data-slider-step="5" data-slider-value="[-100,100]" data-slider-orientation="vertical"
                         data-slider-selection="before" data-slider-tooltip="show" data-slider-id="red">
                  <input type="text" value="" class="slider form-control" data-slider-min="-200" data-slider-max="200"
                         data-slider-step="5" data-slider-value="[-100,100]" data-slider-orientation="vertical"
                         data-slider-selection="before" data-slider-tooltip="show" data-slider-id="blue">
                  <input type="text" value="" class="slider form-control" data-slider-min="-200" data-slider-max="200"
                         data-slider-step="5" data-slider-value="[-100,100]" data-slider-orientation="vertical"
                         data-slider-selection="before" data-slider-tooltip="show" data-slider-id="green">
                  <input type="text" value="" class="slider form-control" data-slider-min="-200" data-slider-max="200"
                         data-slider-step="5" data-slider-value="[-100,100]" data-slider-orientation="vertical"
                         data-slider-selection="before" data-slider-tooltip="show" data-slider-id="yellow">
                  <input type="text" value="" class="slider form-control" data-slider-min="-200" data-slider-max="200"
                         data-slider-step="5" data-slider-value="[-100,100]" data-slider-orientation="vertical"
                         data-slider-selection="before" data-slider-tooltip="show" data-slider-id="aqua">
                  <input type="text" value="" class="slider form-control" data-slider-min="-200" data-slider-max="200"
                         data-slider-step="5" data-slider-value="[-100,100]" data-slider-orientation="vertical"
                         data-slider-selection="before" data-slider-tooltip="show" data-slider-id="purple">
                </div>
              </div>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
    </section>
    <!-- /.content -->
<!-- Modals -->
      <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Modals
        <small>new</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">UI</a></li>
        <li class="active">Modals</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content">
      <div class="callout callout-info">
        <h4>Reminder!</h4>
        Instructions for how to use modals are available on the
        <a href="http://getbootstrap.com/javascript/#modals">Bootstrap documentation</a>
      </div>

      <div class="row">
        <div class="col-xs-12">
          <div class="box box-default">
            <div class="box-header with-border">
              <h3 class="box-title">Modal Examples</h3>
            </div>
            <div class="box-body">
              <button type="button" class="btn btn-default" data-toggle="modal" data-target="#modal-default">
                Launch Default Modal
              </button>
              <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modal-info">
                Launch Info Modal
              </button>
              <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modal-danger">
                Launch Danger Modal
              </button>
              <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#modal-warning">
                Launch Warning Modal
              </button>
              <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal-success">
                Launch Success Modal
              </button>
            </div>
          </div>
        </div>
      </div>

        <div class="modal fade" id="modal-default">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Default Modal</h4>
              </div>
              <div class="modal-body">
                <p>One fine body&hellip;</p>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
              </div>
            </div>
            <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>
        <!-- /.modal -->

        <div class="modal modal-primary fade" id="modal-primary">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Primary Modal</h4>
              </div>
              <div class="modal-body">
                <p>One fine body&hellip;</p>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-outline">Save changes</button>
              </div>
            </div>
            <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>
        <!-- /.modal -->

        <div class="modal modal-info fade" id="modal-info">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Info Modal</h4>
              </div>
              <div class="modal-body">
                <p>One fine body&hellip;</p>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-outline">Save changes</button>
              </div>
            </div>
            <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>
        <!-- /.modal -->

        <div class="modal modal-warning fade" id="modal-warning">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Warning Modal</h4>
              </div>
              <div class="modal-body">
                <p>One fine body&hellip;</p>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-outline">Save changes</button>
              </div>
            </div>
            <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>
        <!-- /.modal -->

        <div class="modal modal-success fade" id="modal-success">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Success Modal</h4>
              </div>
              <div class="modal-body">
                <p>One fine body&hellip;</p>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-outline">Save changes</button>
              </div>
            </div>
            <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>
        <!-- /.modal -->

        <div class="modal modal-danger fade" id="modal-danger">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Danger Modal</h4>
              </div>
              <div class="modal-body">
                <p>One fine body&hellip;</p>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-outline">Save changes</button>
              </div>
            </div>
            <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>
        <!-- /.modal -->
    </section>
    <!-- /.content -->
@endsection
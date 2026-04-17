@extends('layout.app')

@section('content')
@include('frontend.header')
@include('frontend.theme_shadow')
<div class="dashboardarea sp_bottom_100">
    <div class="container-fluid full__width__padding">
        <div class="row">
          @include('components.create_section')
        </div>
    </div>
    <div class="dashboard">
        <div class="container-fluid full__width__padding">
            <div class="row">
                {{-- sidebar menu --}}
                @include('components.sidebar')

                <div class="col-xl-9 col-lg-9 col-md-12">
                    <div class="dashboard__content__wraper">
                        <div class="dashboard__section__title">
                            <h4>Broadcast Message</h4>
                        </div>
                        <div class="row">

                            <div class="tab-content tab__content__wrapper aos-init aos-animate"
                                id="myTabContent" data-aos="fade-up">

                                <div class="tab-pane fade active show" id="projects__one" role="tabpanel"
                                    aria-labelledby="projects__one">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <form action="{{route('dashboard.message.send')}}" method="POST">
                                                @csrf
                                                <div class="col-xl-12">
                                                    <div class="dashboard__form__wraper">
                                                        <div class="dashboard__form__input">
                                                            <label >Message Title</label>
                                                            <input type="text" placeholder="Enter Title" name="title" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-12">
                                                    <div class="dashboard__form__wraper">
                                                        <div class="dashboard__form__input">
                                                            <label >Message Body</label>
                                                            <textarea cols="30" rows="10" class="tiny" name="body"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-12">
                                                    <div class="dashboard__form__button">
                                                        <button type="submit" class="default__button">Send Message</button>
                                                    </div>
                                                </div>
                                            </form>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

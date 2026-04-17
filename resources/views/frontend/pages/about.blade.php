@extends('layout.app')

@section('content')
@include('frontend.header')
@include('frontend.theme_shadow')
<div class="aboutarea__5 sp_bottom_100 sp_top_100">
    <div class="container">
        <div class="row">
            <div class="col-xl-6 col-lg-6" data-aos="fade-up">
                <div class="aboutarea__5__img" data-tilt>
                    <img loading="lazy"  src="img/about/about_14.png" alt="about">
                </div>
            </div>

            <div class="col-xl-6 col-lg-6" data-aos="fade-up">
                <div class="aboutarea__content__wraper__5">
                    <div class="section__title">
                        <div class="section__title__button">
                            <div class="default__small__button">About us</div>
                        </div>
                        <div class="section__title__heading ">
                            <h2>Welcome to the online Learning Center</h2>
                        </div>
                    </div>
                    <div class="about__text__5">
                        <p>Meet my startup design agency Shape Rex Currently I am working at CodeNext as Product Designer.</p>
                    </div>

                    <div class="aboutarea__5__small__icon__wraper">
                        <div class="aboutarea__5__small__icon">
                            <img loading="lazy"  src="img/about/about_15.png" alt="about">

                        </div>
                        <div class="aboutarea__small__heading">
                            <span>10+ Years ExperienceIn</span> this game, Means Product Designing
                        </div>

                    </div>




                    <div class="aboutarea__para__5">
                        <p>I love to work in User Experience & User Interface designing. Because I love to solve the design problem and find easy and better solutions to solve it. I always try my best to make good user interface with the best user
                            experience. I have been working as a UX Designer</p>
                    </div>

                    <div class="aboutarea__bottom__button__5">
                        <a class="default__button" href="#"> More About
                                <i class="icofont-long-arrow-right"></i>
                            </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

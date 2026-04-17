<!doctype html>
<html class="no-js" lang="zxx">


<!-- Mirrored from html.themewin.com/edurock-preview/edurock/dashboard/admin-dashboard.html by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 28 Mar 2024 19:30:39 GMT -->
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>{{$systemDetail[0]->title ?? 'University Digital Transformation Suite (UDTS)'}}</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" type="image/x-icon" href="{{asset('img/fav.jpeg')}}">
    <!-- Place favicon.ico in the root directory -->

    <!-- CSS here -->
    <link rel="stylesheet" href="{{asset('css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('css/animate.min.css')}}">
    <link rel="stylesheet" href="{{asset('css/aos.min.css')}}">
    <link rel="stylesheet" href="{{asset('css/magnific-popup.css')}}">
    <link rel="stylesheet" href="{{asset('css/icofont.min.css')}}">
    <link rel="stylesheet" href="{{asset('css/slick.css')}}">
    <link rel="stylesheet" href="{{asset('css/swiper-bundle.min.css')}}">
    <link rel="stylesheet" href="{{asset('css/style.css')}}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.css">

    {{-- Stack for page-specific styles --}}
    @stack('styles')

    <script>
        // On page load or when changing themes, best to add inline in `head` to avoid FOUC
        if (localStorage.getItem("theme-color") === "dark" || (!("theme-color" in localStorage) && window.matchMedia("(prefers-color-scheme: dark)").matches)) {
            document.documentElement.classList.add("is_dark");
        }
        if (localStorage.getItem("theme-color") === "light") {
            document.documentElement.classList.remove("is_dark");
        }
    </script>
    <script src="https://cdn.tiny.cloud/1/29x31yy541lnbv7bhwkb8eehrwt7mzsc64d3yow8lw3v6y3v/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>

</head>
<body class="body__wrapper">

    {{-- preloader --}}
    @include('components.preloader')
    
    {{-- modern notifications --}}
    @include('components.modern-notifications')
    
    {{-- confirmation modal --}}
    @include('components.confirmation-modal')
    
    {{-- Calendar Modal --}}
    @include('components.calendar-modal')

    {{-- Maintenance Countdown Banner --}}
    @include('components.maintenance-countdown')

    <main class="main_wrapper overflow-hidden">
        @include('components.password-reminder')
        @yield('content')

        @include('components.footer')
    </main>

   <!-- JS here -->
   <script src="{{asset('js/vendor/modernizr-3.5.0.min.js')}}"></script>
   <script src="{{asset('js/vendor/jquery-3.6.0.min.js')}}"></script>
   <!-- DataTables JS -->
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.js"></script>
   <script src="{{asset('js/popper.min.js')}}"></script>
   <script src="{{asset('js/bootstrap.min.js')}}"></script>
   <script src="{{asset('js/isotope.pkgd.min.js')}}"></script>
   <script src="{{asset('js/slick.min.js')}}"></script>
   <script src="{{asset('js/jquery.meanmenu.min.js')}}"></script>
   <script src="{{asset('js/ajax-form.js')}}"></script>
   <script src="{{asset('js/wow.min.js')}}"></script>
   <script src="{{asset('js/jquery.scrollUp.min.js')}}"></script>
   <script src="{{asset('js/imagesloaded.pkgd.min.js')}}"></script>
   <script src="{{asset('js/jquery.magnific-popup.min.js')}}"></script>
   <script src="{{asset('js/waypoints.min.js')}}"></script>
   <script src="{{asset('js/jquery.counterup.min.js')}}"></script>
   <script src="{{asset('js/plugins.js')}}"></script>
   <script src="{{asset('js/swiper-bundle.min.js')}}"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js"></script>
   <script src="{{asset('js/main.js')}}"></script>

   <script>
    tinymce.init({
      selector: 'textarea.tiny',
      plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
      toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
    });
  </script>
  {{-- Datatables --}}
  <script>
    $(document).ready(function() {
        // Only initialize DataTables if there are tables with the 'example' class
        // and ensure they have proper structure
        $('.example').each(function() {
            if ($(this).find('thead th').length > 0 && $(this).find('tbody tr').length > 0) {
                try {
                    $(this).DataTable({
                        "searching": true,
                        "responsive": true,
                        "autoWidth": false
                    });
                } catch (error) {
                    console.log('DataTables initialization skipped for table:', this);
                }
            }
        });
    });
    </script>
   
   {{-- Stack for page-specific scripts --}}
   @stack('scripts')
{{-- filtering --}}
<script>
    $(document).ready(function() {
        $('.filter-checkbox').click(function(event) {
            // event.preventDefault();
            // $(this).prop('checked', true);
            var facultyId = $('.faculty-checkbox').val();
            var tags = [];
            var semesters = [];
            var years = [];

            // Get selected tags
            $('.tag-checkbox:checked').each(function() {
                tags.push($(this).val());
            });

            // Get selected semesters
            $('.semester-checkbox:checked').each(function() {
                semesters.push($(this).val());
            });

            // Get selected years
            $('.year-checkbox:checked').each(function() {
                years.push($(this).val());
            });

            $.ajax({
                url: '/exams/filter',
                type: 'GET',
                data: {
                    faculty_id: facultyId,
                    tags: tags,
                    semesters: semesters,
                    years: years
                },
                success: function(response) {
                    var examList = $('#exam-list');
                    examList.empty();

                    if (response.length > 0) {
                        $.each(response, function(index, exam) {

                            var examHtml = `
                                <div class="col-xl-4 col-lg-6 col-md-12 col-sm-6 col-12" data-aos="fade-up">
                                    <div class="gridarea__wraper gridarea__wraper__2">
                                        <div class="gridarea__img">
                                            <a href="${exam.exam_document}" download>
                                                ${exam.exam_document.includes('.pdf') ?
                                                    '<img loading="lazy"  src="/img/pdf.jpg" alt="PDF">' :
                                                    '<img loading="lazy"  src="/img/word.png" alt="Word">'
                                                }
                                            </a>
                                            <div class="gridarea__small__button">
                                                <div class="grid__badge">${exam.course_code}</div>
                                            </div>
                                            <div class="gridarea__small__icon">
                                                {{-- <a href="#"><i class="icofont-heart-alt"></i></a> --}}
                                            </div>
                                        </div>
                                        <div class="gridarea__content">
                                            <div class="gridarea__list">
                                                <ul>
                                                    <li>
                                                        <i class="icofont-book-alt"></i> ${exam.exam_format}
                                                    </li>
                                                    <li>
                                                        <i class="icofont-clock-time"></i> ${exam.duration}
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="gridarea__heading">
                                                <h3><a href="#">${exam.course_title}</a></h3>
                                            </div>
                                            <div class="gridarea__price">
                                                <span style="font-size: 14px"> <a href="${exam.exam_document}" download><i class="fas fa-download"></i> Paper </a></span>
                                                <span style="font-size: 14px"> <a href="${exam.answer_key}" download><i class="fas fa-download"></i> Answer Key</a></span>
                                            </div>
                                            <div class="gridarea__bottom">
                                                <a href="instructor-details.html">
                                                    <div class="gridarea__small__img">
                                                        <img loading="lazy"  src="/img/grid/grid_small_1.jpg" alt="Instructor">
                                                        <div class="gridarea__small__content">
                                                            <h6>${exam.instructor_name}</h6>
                                                        </div>
                                                    </div>
                                                </a>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                            examList.append(examHtml);
                        });
                    }
                    else {
                        examList.html('<h5 class="text-center">No Exams Found!</h5>');
                    }
                },
                error: function(xhr, status, error) {
                    // Handle errors
                }
            });
        });
    });
</script>
<script>
    const triggerLogoModal = document.getElementById('triggerLogoModal');
    if (triggerLogoModal) {
        triggerLogoModal.addEventListener('click', function (event) {
            event.preventDefault();
            $('#myLogoModal').modal('show');
        });
    }
</script>
<script>
    const triggerDepartmentModal = document.getElementById('triggerDepartmentModal');
    if (triggerDepartmentModal) {
        triggerDepartmentModal.addEventListener('click', function (event) {
            event.preventDefault();
            $('#myDepartmentModal').modal('show');
        });
    }
</script>
<script>
    const triggerAcademicModal = document.getElementById('triggerAcademicModal');
    if (triggerAcademicModal) {
        triggerAcademicModal.addEventListener('click', function (event) {
            event.preventDefault();
            $('#myAcademicModal').modal('show');
        });
    }
</script>
</body>
</html>

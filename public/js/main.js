var App = {
  base: jQuery("base").attr("href"),
  jsonData: null,
  contactModal: false,
  isLoading: false,

  init: function () {
    //this.stickyHeader();
    this.heroSlider();
    this.toggleDarkMode();
    this.toggleMobileMenu();
    this.dropzoneUpload();
    this.timeLine();    

    // Nice Select
    if ($("select")[0]) {
      $("select").niceSelect();
    }

    this.contactModal = new bootstrap.Modal("#contactModal");
  },

  /**
   *
   * heroSlider
   *
   */
  heroSlider: function () {
    // Featured Slider Two
    if ($(".f-2-slider")[0]) {
      $(".f-2-slider.owl-carousel").owlCarousel({
        items: 1,
        loop: true,
        nav: true,
        navText: [
          "<i class='fa-solid fa-arrow-left'></i>",
          "<i class='fa-solid fa-arrow-right'></i>",
        ],
        dots: false,
        touchDrag: true,
        mouseDrag: false,
        margin: 10,
        navContainer: ".f-2-s-nav",
      });
    }
  },

  /**
   * toggle dark mode
   */
  toggleDarkMode: function () {
    let lightmode = localStorage.getItem("light-d");

    const lightmodeToggle = document.querySelector("#theme-icon");
    const enableLightMode = () => {
      document.body.classList.add("light-d");
      localStorage.setItem("light-d", "enabled");
      lightmodeToggle.src = "imgs/moon.png";
    };

    const disablelightmode = () => {
      document.body.classList.remove("light-d");
      localStorage.setItem("light-d", null);
      lightmodeToggle.src = "imgs/sun.png";
    };

    if (lightmode === "enabled") {
      enableLightMode();
    }

    lightmodeToggle.addEventListener("click", () => {
      lightmode = localStorage.getItem("light-d");

      if (lightmode !== "enabled") {
        enableLightMode();
      } else {
        disablelightmode();
      }
    });
  },

  toggleMobileMenu: function () {
    
    /*$(".mobile-nav .menu-item-has-children").on("click", function (event) {
      $(this).toggleClass("active");
      event.stopPropagation();
    });*/

    $("#mobile-menu").click(function () {
      $(this).toggleClass("open");
      $("#mobile-nav").toggleClass("open");
    });

    $("#desktop-menu").click(function () {
      $(this).toggleClass("open");
      $(".desktop-menu").toggleClass("open");
    });

    $("#res-cross").click(function () {
      $("#mobile-nav").removeClass("open");
      $("#mobile-menu").removeClass("open");
    });

    $(".mobile-nav li a").click(function () {
      if( $(this).attr("href").indexOf("#") >= 0 )
      {
        $("#mobile-nav").removeClass("open");
        $("#mobile-menu").removeClass("open");
      }      
    });
    
  },

  stickyHeader: function () {
    let new_scroll_position = 0;
    let last_scroll_position = 0;
    const header = document.getElementById("stickyHeader");

    window.addEventListener("scroll", function (e) {
      last_scroll_position = window.scrollY;

      // Scrolling down

      if (
        new_scroll_position < last_scroll_position &&
        last_scroll_position > 100
      ) {
        // header.removeClass('slideDown').addClass('slideUp');
        header.classList.remove("slideDown");
        header.classList.add("slideUp");
        // Scroll top
      } else if (last_scroll_position < 100) {
        header.classList.remove("slideDown");
      } else if (new_scroll_position > last_scroll_position) {
        header.classList.remove("slideUp");
        header.classList.add("slideDown");
      }

      new_scroll_position = last_scroll_position;
    });
  },

  scrollTop: function () {

    window.onscroll = () => {
      var num = window.pageYOffset;
      if (num >= 160) {
        document.querySelector("#scrollTop").classList.add("active");
      } else {
        document.querySelector("#scrollTop").classList.remove("active");
      }
    };

    document.querySelector("#scrollTop").addEventListener("click", function () {
      window.scrollTo({
        top: 0,
        left: 0,
        behavior: "smooth",
      });
    });
  },

  /**
   * 
   * timeline scrolling
   * 
   */
  timeLine: function() {

    window.onscroll = function() {
        var num = window.pageYOffset;
        $('#timeline').waypoint(function() {
            $(".fill").css("height", num);
        }, {
            offset: '100%'
        });        
    }

  },

  /**
   *
   * show page mask
   *
   */
  showMask: function (callback) {
    // $("body").removeClass("page-loaded");
    jQuery("#preloader").show(() => {
      if (callback) {
        callback();
      }
    });
  },

  /**
   *
   * hide page mask
   *
   */
  hideMask: function () {
    // $("body").addClass("page-loaded");
    jQuery("#preloader").delay(350).fadeOut("slow");
  },

  /**
   * 
   * hibaüzenet
   * 
   * @param {*} form 
   * @param {*} message 
   */
  showError: function (form, message) { 
    jQuery(".messages", form).html(message);
  },

  /**
   * 
   * hibaüzenet törlése
   * 
   */
  clearError : function() {
    jQuery(".messages", form).html('');
  },

  /**
   *
   * submit contact form
   *
   * @param {*} btn
   */
  submitContact: function (btn) {
    const form = jQuery(btn).closest("form");
    this.submitForm(btn, form);
  },

  /**
   *
   * submit form
   *
   * @param {*} btn
   * @param {*} form
   *
   */
  submitForm: function (btn, form) {
    var formData = new FormData(form[0]);

    jQuery.ajax({
      url: form.attr("action"),
      type: "post",
      //data: form.serializeArray(),
      data: formData,
      dataType: "json",
      contentType: false,
      processData: false,
      beforeSend: () => {
        this.isLoading = true;
        setTimeout(() => {
          if (this.isLoading) {
            this.showMask(() => {
              jQuery(".messages", form).html("");
            });
          }
        }, 500);
        jQuery(btn).attr("disabled", true);
      },
      complete: (response) => {
        this.isLoading = false;
        this.hideMask();
        jQuery(btn).attr("disabled", false);
        jQuery('input[name="csrf_test_name"]', form).val(
          response.responseJSON.token
        );
      },
      success: (json) => {
        if (json.redirect) {
          window.location = json.redirect;
        } else {
          form[0].reset();
          const title = json.title || "SIKER";
          
        }
      },
      error: (xhr, ajaxOptions, thrownError) => {
        const resp = JSON.parse(xhr.responseText);
        jQuery(".messages", form).html(resp.message);
      },
    });
  },

  /**
   *
   * send form with dropzone file upload
   *
   */
  dropzoneUpload: function () {
    
    const forms = document.querySelectorAll("form");
    forms.forEach(form => {      
      this.initDropzone(form);
    });
    
  },

  
  /**
   * 
   * init dropzone
   * 
   * @param {*} form 
   */
  initDropzone: function (form) { 

    Dropzone.autoDiscover = false;

    form = jQuery(form);
    const me = this;
    const maxFiles = 5;

    $("div.dropzone", form).dropzone({
      url: form.attr("action"),
      paramName: "photos",
      autoProcessQueue: false,
      uploadMultiple: true,
      parallelUploads: 10,
      maxFiles: maxFiles,
      acceptedFiles: ".jpeg,.jpg,.png,.webp,.heic,.heif,.pdf",
      addRemoveLinks: true,
      dictDefaultMessage: $('div.dropzone', form).data("title"),
      dictFallbackMessage:
        "A böngésződ nem támogatja a \"drag'n'drop\" fájlfeltöltést.",
      dictFallbackText:
        "Kérjük, használd az alábbi űrlapot a fájlok feltöltéséhez, mint a régi időkben.",
      dictFileTooBig:
        "A fájl túl nagy ({{filesize}}MiB). Maximális fájlméret: {{maxFilesize}}MiB.",
      dictInvalidFileType: "Ilyen típusú fájlokat nem tudsz feltölteni.",
      dictResponseError: "A szerver {{statusCode}} kóddal válaszolt.",
      dictCancelUpload: "Feltöltés megszakítása",
      dictCancelUploadConfirmation:
        "Biztos, hogy megszakítod ezt a feltöltést?",
      dictRemoveFile: "Fájl eltávolítása",
      dictMaxFilesExceeded: "Nem tudsz több fájlt feltölteni.",

      // The setting up of the dropzone
      init: function () {

        var myDropzone = this;

        // First change the button to actually tell Dropzone to process the queue.
        $("button.submit", form).on("click", (e) => {
          e.preventDefault();
          e.stopPropagation();
          if (myDropzone.files.length > 0) {
            myDropzone.processQueue();
          } else {
            App.submitForm($(this), form);
          }
        });

        this.on("maxfilesexceeded", function (file) {
          this.removeFile(file);
          me.showError(form, `<div class="alert alert-danger">Maximum ${maxFiles} fájl tölthető fel!</div>`);          
        });
        this.on("sending", (file, xhr, formData) => {
          $("input, textarea", form).each((idx, el) => {
            if (
              $(el).attr("type") == "radio" ||
              $(el).attr("type") == "checkbox"
            ) {
              let vals = [];
              $(el).each(function () {
                if (this.checked) {
                  vals.push(this.value);
                }
              });
              if (vals.length > 0) formData.append($(el).attr("name"), vals);
            } else formData.append($(el).attr("name"), $(el).val());
          });
          App.showMask();
        });
        this.on("complete", (files, response) => {
          App.hideMask();
        });
        this.on("successmultiple", (files, response) => {
          if (response.redirect) {
            window.location = response.redirect;
          } else {
            form[0].reset();
            const title = response.title || "SIKER";            
          }
        });
        this.on("errormultiple", (files, response) => {
          //const resp = JSON.parse(xhr.responseText);
          $.each(files, function (i, file) {
            file.status = Dropzone.QUEUED;
            file.upload.progress = 0;
            file.upload.bytesSent = 0;
            $(file.previewElement).removeClass("dz-processing");
            $(file.previewElement).removeClass("dz-error");
            $(file.previewElement).removeClass("dz-complete");
          });
          me.showError(form, (typeof response == "object" ? response.message : response));                    
        });
      },
    });

  },

  autoPlayYouTubeModal: function() {
    var triggerOpen = $("body").find('[data-tagVideo]');
    triggerOpen.click(function() {
      var theModal = $(this).data("bs-target"),
        videoSRC = $(this).attr("data-tagVideo"),
        videoSRCauto = videoSRC + "?autoplay=1&rel=0";
      $(theModal + ' iframe').attr('src', videoSRCauto);
      $(theModal + ' button.btn-close').click(function() {
        $(theModal + ' iframe').attr('src', videoSRC);
      });
    });
  },

  counter: function() {

    $(document).scroll(function () {
      $('.odometer').each(function () {
        var parent_section_postion = $(this).closest('section').position();
        var parent_section_top = parent_section_postion.top;
        if ($(document).scrollTop() > parent_section_top - ($(window).height() - 200) ) {          
          if ($(this).data('status') == 'yes') {
            $(this).html($(this).data('count'));
            $(this).data('status', 'no');
          }
        }
      });
    });

  }

};

jQuery(document).ready(function () {
  App.init();
  AOS.init({
    once: true,
  });
  // App.scrollTop();
  App.autoPlayYouTubeModal();
  // App.counter();
  // enable tooltips
  const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
  const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

});

jQuery(window).on("load", function () {
  App.hideMask();
  //App.contactModal.show();
});

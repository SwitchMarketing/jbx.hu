var App = {
  base: jQuery("base").attr("href"),
  jsonData: null,
  contactModal: false,
  isLoading: false,

  init: function () {
    //this.stickyHeader();
    this.heroSlider();
    this.testimonialsSlider();
    this.toggleDarkMode();
    this.toggleMobileMenu();
    this.dropzoneUpload();
    this.timeLine();
    this.hideCTAButton();

    this.categoryTree();
    this.updateOptionAvailability();
    this.initOptionPillToggles();
    this.scrollActiveOptionPillsIntoView();

    this.nzoomimg();
    this.pdGallery();

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
   * testimonials slider
   */
  testimonialsSlider: function () {
    
    if ($(".client-review-slider")[0]){
        $('.client-review-slider.owl-carousel').owlCarousel({
            items:1,
            autoplay: true,
            autoplayTimeout: 3000,
            autoplayHoverPause: false,
            dots: true,
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
      if ($(this).attr("href").indexOf("#") >= 0) {
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
  timeLine: function () {
    window.onscroll = function () {
      var num = window.pageYOffset;
      $("#timeline").waypoint(
        function () {
          $(".fill").css("height", num);
        },
        {
          offset: "100%",
        }
      );
    };
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
  clearError: function () {
    jQuery(".messages", form).html("");
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
   * submit order form
   * 
   * @param {*} btn 
   */
  submitOrder: function (btn) {
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
    forms.forEach((form) => {
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
      dictDefaultMessage: $("div.dropzone", form).data("title"),
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
          me.showError(
            form,
            `<div class="alert alert-danger">Maximum ${maxFiles} fájl tölthető fel!</div>`
          );
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
          me.showError(
            form,
            typeof response == "object" ? response.message : response
          );
        });
      },
    });
  },

  autoPlayYouTubeModal: function () {
    var triggerOpen = $("body").find("[data-tagVideo]");
    triggerOpen.click(function () {
      var theModal = $(this).data("bs-target"),
        videoSRC = $(this).attr("data-tagVideo"),
        videoSRCauto = videoSRC + "?autoplay=1&rel=0";
      $(theModal + " iframe").attr("src", videoSRCauto);
      $(theModal + " button.btn-close").click(function () {
        $(theModal + " iframe").attr("src", videoSRC);
      });
    });
  },

  counter: function () {
    $(document).scroll(function () {
      $(".odometer").each(function () {
        var parent_section_postion = $(this).closest("section").position();
        var parent_section_top = parent_section_postion.top;
        if (
          $(document).scrollTop() >
          parent_section_top - ($(window).height() - 200)
        ) {
          if ($(this).data("status") == "yes") {
            $(this).html($(this).data("count"));
            $(this).data("status", "no");
          }
        }
      });
    });
  },

  setCTAButtonPosition: function () {
    if ($("a#btn-bottom-cta").length > 0) {
      let windowWidth = $(window).width();
      let pos = 40;

      if (windowWidth > 768) {
        let footerContainerWidth = $("footer .container").outerWidth();
        pos = (windowWidth - footerContainerWidth) / 2;
      }

      $("a#btn-bottom-cta").css({
        right: pos + "px",
      });
    }
  },

  hideCTAButton: function () {
    if ("IntersectionObserver" in window) {
      const buttonToHide = document.querySelector("a.btn-bottom-cta");

      const hideWhenBoxInView = new IntersectionObserver((entries) => {
        if (entries[0].intersectionRatio <= 0) {
          buttonToHide.classList.add("visible");
        } else {
          buttonToHide.classList.remove("visible");
        }
      });

      if (document.getElementById("contact_form"))
        hideWhenBoxInView.observe(document.getElementById("contact_form"));
      else buttonToHide.classList.add("visible");
    }
  },

  nzoomimg: function () {
    
    let t = document.getElementById("NZoomImg");

    if(!t) return false;
    
    let e = t.getAttribute("data-NZoomscale") <= 0 ? 1 : t.getAttribute("data-NZoomscale"),
        s = t.clientWidth,
        o = t.clientHeight;


    $("#NZoomImg").replaceWith(
      '<div id="NZoomContainer">' + t.outerHTML + "</div>"
    );
    let i = $("#NZoomContainer"),
      n = $("#NZoomImg");
    i.css("width", s + "px"),
      i.css("height", o + "px"),
      i.mousemove(function (t) {
        let e = $(this).offset(),
          i =
            ((t.pageX - e.left) / s) * 100 <= 100
              ? ((t.pageX - e.left) / s) * 100
              : 100,
          c =
            ((t.pageY - e.top) / o) * 100 <= 100
              ? ((t.pageY - e.top) / o) * 100
              : 100;
        n.css("transform-origin", i + "% " + c + "%");
      }),
      i
        .mouseenter(function () {
          n.css("cursor", "crosshair"),
            n.css("width", s + "px"),
            n.css("height", o + "px"),
            n.css("transition", "0.2s"),
            n.css("transform", "scale(" + e + ")");
        })
        .mouseleave(function () {
          n.css("transition", "0.2s"), n.css("transform", "scale(1)");
        });
  },

  pdGallery : function() {

    $('.li-pd-imgs').on('click', function() {

      var img_src = "";

      $('.li-pd-imgs.nav-active').removeClass('nav-active');

      $(this).addClass('nav-active');

      img_src = $(this).find('img').attr('src');

      $('#NZoomContainer').children('img').attr('src', img_src);

    });

  },

  toggleDeliveryAddr: function(checkbox) {

    // check if checkbox is checked
    if ($(checkbox).is(':checked')) {
      $('div#deliveryAddr').removeClass('d-none');
    } else {
      $('div#deliveryAddr').addClass('d-none');
    }    

  },

  categoryTree: function () {

    if($('.shop-categories').length > 0) {
      
      $('.shop-categories li.has-children > .tree-toggle').on('click', function(e) {
        e.stopPropagation();
        $(this).parent().toggleClass('collapsed').toggleClass('expanded');
      });

      // find active li elements with class 'active' and expand parents
      $('.shop-categories li.active').parents('li.has-children').removeClass('collapsed').addClass('expanded');
      // az aktív li maga is nyíljon ki, ha szülő kategória
      $('.shop-categories li.active.has-children').removeClass('collapsed').addClass('expanded');

    }
  },

  productOption: function(option) {

    if ($(option).is(':disabled')) {
      return;
    }

    const masterSlug = $(option).closest('.option-pills-container').data('master-slug') || '';
    const clickedOptionId = $(option).data('option-id');
    const clickedOptionValue = $(option).data('option-value');
    const params = [];
    const activeButtons = $('button.option-pill.active') || [];

    params.push({
      optionId : $(option).data('option-id'),
      optionValue : $(option).data('option-value')
    });

    if (activeButtons.length > 0) {
      // remove active class from all buttons
      // activeButtons.removeClass('active');
      activeButtons.each(function(el) {        
        if($(option).data('option-id') != $(this).data('option-id')) {
          params.push(
            {
              optionId : $(this).data('option-id'),
              optionValue : $(this).data('option-value')
            }
          );
        }        
      });      
    }

    if(masterSlug != '' && params.length > 0) {
        const wrap = $(option).closest('.option-pills-wrap').get(0);
        if (wrap && typeof option.scrollIntoView === 'function') {
          option.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }

        // ajax call to get product data
        $.ajax({
          url: App.base + 'termek',
          type: 'POST',
          data: {
            master_slug: masterSlug,
            clicked_option_id: clickedOptionId,
            clicked_option_value: clickedOptionValue,
            params: JSON.stringify(params)
          },
          dataType: 'json',
          success: function(response) {
            window.location = response.url;            
          },
          error: function(xhr) {
            const resp = xhr.responseJSON || {};
            if (resp.error) {
              App.showCartAlert(resp.error, 'warning');
            }
          }
        });
    }
    
  },

  updateOptionAvailability: function() {
    const normalizeOptionValue = function(value) {
      return String(value || '')
        .replace(/\s+/g, ' ')
        .trim()
        .toLowerCase();
    };

    const container = $('.option-pills-container');
    if (!container.length) {
      return;
    }

    const matrixJson = container.attr('data-option-matrix') || '[]';
    let matrix = [];
    try {
      matrix = JSON.parse(matrixJson);
    } catch (e) {
      matrix = [];
    }

    if (!Array.isArray(matrix) || matrix.length === 0) {
      return;
    }

    const activeSelections = {};
    $('button.option-pill.active').each(function() {
      const optionId = String($(this).data('option-id'));
      const optionValue = normalizeOptionValue($(this).data('option-value'));
      activeSelections[optionId] = optionValue;
    });

    $('button.option-pill').each(function() {
      const btn = $(this);
      const optionId = String(btn.data('option-id'));
      const optionValue = normalizeOptionValue(btn.data('option-value'));

      const variantsWithOption = matrix.filter(function(variant) {
        const attrs = variant.attrs || {};
        return normalizeOptionValue(attrs[optionId]) === optionValue;
      });

      const isSelectable = variantsWithOption.length > 0;
      const candidateSelections = Object.assign({}, activeSelections, {
        [optionId]: optionValue,
      });

      const isStrictlyCompatible = matrix.some(function(variant) {
        const attrs = variant.attrs || {};
        return Object.keys(candidateSelections).every(function(key) {
          return normalizeOptionValue(attrs[key]) === String(candidateSelections[key]);
        });
      });

      btn.prop('disabled', !isSelectable);
      btn.toggleClass('unavailable', !isSelectable);
      btn.toggleClass('partially-compatible', isSelectable && !isStrictlyCompatible);

      if (!isSelectable) {
        btn.attr('title', 'Ez az opció nem elérhető.');
      } else if (!isStrictlyCompatible) {
        btn.attr('title', 'Választható, de más opciók is automatikusan változnak.');
      } else {
        btn.attr('title', '');
      }
    });
  },

  initOptionPillToggles: function() {
    $('.option-pills-toggle').off('click').on('click', function() {
      const toggle = $(this);
      const wrap = toggle.closest('.option-pills-wrap');
      const isCollapsed = wrap.hasClass('is-collapsed');
      const collapsedLabel = toggle.data('collapsed-label') || 'Tovabbi opciok';
      const expandedLabel = toggle.data('expanded-label') || 'Kevesebb opcio';

      if (isCollapsed) {
        wrap.removeClass('is-collapsed');
        toggle.text(expandedLabel);
      } else {
        wrap.addClass('is-collapsed');
        toggle.text(collapsedLabel);
      }
    });
  },

  scrollActiveOptionPillsIntoView: function() {
    $('.option-pills-wrap').each(function() {
      const active = $(this).find('.option-pill.active').get(0);
      if (active && typeof active.scrollIntoView === 'function') {
        active.scrollIntoView({ behavior: 'auto', block: 'nearest', inline: 'center' });
      }
    });
  },

  addToCart: function(btn) {

    const self = this;
    const variantId = $(btn).data('variant-id') || '';
    const sku = $(btn).data('sku') || '';
    const qty = $('#qty-' + sku).val() || 1;

    if(variantId != '' || sku != '') {
      $.ajax({
        url: App.base + 'kosar',
        type: 'POST',
        data: {
          variant_id: variantId,
          sku: sku,
          qty: qty
        },
        dataType: 'json',
        beforeSend: () => { 
          $(btn).attr('disabled', true);
        },
        complete: () => {
          $(btn).attr('disabled', false);
        },
        success: function(response) {
          if(response.success) {  
            self.showCartAlert(response.message, 'success');            
          }
        }
      });

    }
  },

  removeFromCart: function(btn) {

    const self = this;
    const lineId = $(btn).data('line-id') || '';
    const sku = $(btn).data('sku') || '';
    if(lineId != '' || sku != '') {
      $.ajax({
        url: App.base + 'kosar/torles',
        type: 'POST',
        data: {
          line_id: lineId,
          sku: sku
        },
        dataType: 'json',
        beforeSend: () => {
          $(btn).attr('disabled', true);
        },
        complete: () => {
          $(btn).attr('disabled', false);
        },
        success: function(response) {
          window.location.reload();
        }
      });

    }
  },

  changeQty: function(btn, diff) {

    const $input = $(btn).closest('.qty-control').find('.qty-input');
    if(!$input.length) {
      return;
    }

    const current = parseInt($input.val(), 10) || 1;
    const next = Math.max(1, current + parseInt(diff, 10));
    $input.val(next);
    this.updateCartQty($input.get(0));

  },

  updateCartQty: function(input) {

    const self = this;
    const $input = $(input);
    const lineId = $input.data('line-id') || '';
    let qty = parseInt($input.val(), 10);

    if(!Number.isInteger(qty) || qty < 1) {
      qty = 1;
      $input.val(qty);
    }

    if(lineId === '') {
      return;
    }

    const $control = $input.closest('.qty-control');
    $control.find('.qty-btn').attr('disabled', true);
    $input.attr('disabled', true);

    $.ajax({
      url: App.base + 'kosar/mennyiseg',
      type: 'POST',
      data: {
        line_id: lineId,
        qty: qty,
      },
      dataType: 'json',
      complete: function() {
        $control.find('.qty-btn').attr('disabled', false);
        $input.attr('disabled', false);
      },
      success: function(response) {
        if(response.success) {
          window.location.reload();
        } else {
          self.showCartAlert('A mennyiség frissítése sikertelen.', 'danger');
        }
      },
      error: function() {
        self.showCartAlert('A mennyiség frissítése sikertelen.', 'danger');
      }
    });

  },

  showCartAlert: function(message, type = 'success') {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show shadow" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;

    const container = $('#cartMessages');
    container.html(alertHtml);

    // Automatikus eltűnés 3 másodperc után
    setTimeout(() => {
        const alertEl = container.find('.alert');
        const bsAlert = new bootstrap.Alert(alertEl[0]);
        bsAlert.close();
    }, 3000);
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
  const tooltipTriggerList = document.querySelectorAll(
    '[data-bs-toggle="tooltip"]'
  );
  const tooltipList = [...tooltipTriggerList].map(
    (tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl)
  );
});

jQuery(window).on("load", function () {
  App.hideMask();
  //App.contactModal.show();
});

jQuery(window).on("load", function () {
  App.hideMask();
  //App.contactModal.show();
  
});

$(window).on("DOMContentLoaded load resize", function () {
  App.setCTAButtonPosition();
});

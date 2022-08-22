/*
 * Custom code goes here.
 * A template should always ship with an empty custom.js
 */
 jQuery(window).scroll(function(){
   if( jQuery(this).scrollTop() > 100 ){
     jQuery('.contenido-extensible:not(.extendido)').addClass('comprimido');
   }
 });
 //Acción del boton de SEO
 if(jQuery('#interruptor-seo').length > 0){
   jQuery('#interruptor-seo').on('click',function(e){
     e.preventDefault();

     if(jQuery('.contenido-extensible').hasClass('comprimido')){
       jQuery('.contenido-extensible').removeClass('comprimido');
       jQuery('.contenido-extensible').addClass('extendido');
       jQuery(this).removeClass('mas');
       jQuery(this).addClass('menos');
     } else{
       jQuery('.contenido-extensible').removeClass('extendido');
       jQuery('.contenido-extensible').addClass('comprimido');
       jQuery(this).removeClass('menos');
       jQuery(this).addClass('mas');
     }
   });
 }

 $(document).ready(function(){
   if (window.matchMedia("(max-width: 575px)").matches) {
     $('.products.slick').slick({
             infinite: true,
             autoplaySpeed: 6000,
             draggable: true,
             dots: false,
             arrows: true,
             autoplay: true,
             slidesToShow: 1,
             slidesToScroll: 1
         }
     );
   }
 });

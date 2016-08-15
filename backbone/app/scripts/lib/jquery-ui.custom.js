define(["jquery", 'jquery-ui-origin'],function($) {
	'use strict';
	setTimeout(function(){
		$.datepicker.setDefaults({
			closeText: '閉じる',
			prevText: '&#x3c;前',
			nextText: '次&#x3e;',
			currentText: '今日',
			monthNames: ['1月','2月','3月','4月','5月','6月',
				'7月','8月','9月','10月','11月','12月'],
			monthNamesShort: ['1月','2月','3月','4月','5月','6月',
				'7月','8月','9月','10月','11月','12月'],
			dayNames: ['日曜日','月曜日','火曜日','水曜日','木曜日','金曜日','土曜日'],
			dayNamesShort: ['日','月','火','水','木','金','土'],
			dayNamesMin: ['日','月','火','水','木','金','土'],
			weekHeader: '週',
			dateFormat: 'yy.mm.dd',
			firstDay: 0,
			isRTL: false,
			showMonthAfterYear: true,
			yearSuffix: '年'
		});
	},1);
	/**
	 * jquery.uiのdialogでoverlayをクリックすると閉じる様に改造
	 */
	/*
	if (typeof $.widget === 'function') {
		$.widget( "custom.dialog", $.ui.dialog, {
			_createOverlay: function() {
				this._super();
				var that = this;
				if ( this.overlay ) {
					this.overlay.on('click', function(e){
						that.close(e);
					});
				}
			},
			_destroyOverlay: function() {
				if ( !this.options.modal ) {
					return;
				}
				if ( this.overlay ) {
					this.overlay.off('click');
					this._super();
				}
			},
			_createWrapper: function() {//ダブルクリックで閉じる様にした。
				$.ui.dialog.prototype._createWrapper.call(this);
				this._on( this.uiDialog, {
					dblclick: function (event) {
						this.close(event);
						return;
					}
				});
			}
		});
	}
	*/
	if (typeof $.widget === 'function') {
		$.widget("custom.dialog", $.ui.dialog, {
			options: {
				appendTo: "body",
				autoOpen: true,
				buttons: [],
				closeOnEscape: true,
				closeText: "Close",
				dialogClass: "",
				draggable: true,
				hide: null,
				height: "auto",
				maxHeight: Math.floor($(window).height() * 0.98),
				maxWidth: null,
				minHeight: 150,
				minWidth: 150,
				modal: false,
				position: {
					my: "center",
					at: "center",
					of: window,
					collision: "fit",
					// Ensure the titlebar is always visible
					using: function( pos ) {
						var topOffset = $( this ).css( pos ).offset().top;
						if ( topOffset < 0 ) {
							$( this ).css( "top", pos.top - topOffset );
						}
					}
				},
				resizable: true,
				show: null,
				title: null,
				width: 300,

				// callbacks
				beforeClose: null,
				close: null,
				drag: null,
				dragStart: null,
				dragStop: null,
				focus: null,
				open: null,
				resize: null,
				resizeStart: null,
				resizeStop: null
			},
			_moveToTop: function( event, silent ) {
				var moved = false,
					zIndicies = this.uiDialog.siblings( ".ui-front:visible" ).map(function() {
						return +$( this ).css( "z-index" );
					}).get(),
					zIndexMax = Math.max.apply( null, zIndicies );

				if ( zIndexMax >= +this.uiDialog.css( "z-index" ) ) {
					this.uiDialog.css( "z-index", zIndexMax + 2 );
					moved = true;
				}

				if ( moved && !silent ) {
					this._trigger( "focus", event );
				}
				return moved;
			},
			_createOverlay: function() {

				var zIndicies = this.uiDialog.siblings( ".ui-front:visible" ).map(function() {
					return +$( this ).css( "z-index" );
				}).get();
				zIndicies.push(100);
				var zIndexMax = Math.max.apply( null, zIndicies );

				if ( !this.options.modal ) {
					return;
				}

				// We use a delay in case the overlay is created from an
				// event that we're going to be cancelling (#2804)
				var isOpening = true;
				this._delay(function() {
					isOpening = false;
				});

				if ( !this.document.data( "ui-dialog-overlays" ) ) {

					// Prevent use of anchors and inputs
					// Using _on() for an event handler shared across many instances is
					// safe because the dialogs stack and must be closed in reverse order
					this._on( this.document, {
						focusin: function( event ) {
							if ( isOpening ) {
								return;
							}

							if ( !this._allowInteraction( event ) ) {
								event.preventDefault();
								this._trackingInstances()[ 0 ]._focusTabbable();
							}
						}
					});
				}

				this.overlay = $( "<div>" )
					.addClass( "ui-widget-overlay ui-front" )
					.appendTo( this._appendTo() );
				this._on( this.overlay, {
					mousedown: "_keepFocus"
				});

				this.overlay.css( "z-index", zIndexMax + 1 );

				this.document.data( "ui-dialog-overlays",
					(this.document.data( "ui-dialog-overlays" ) || 0) + 1 );
			}
		});
	}
});

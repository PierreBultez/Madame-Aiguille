/**
 * Panneau « Rédiger avec l'IA » de la section Contenu du formulaire produit (lot 10).
 *
 * Analyser → répondre → générer → aperçu → insérer dans les champs cochés. Le navigateur n'appelle jamais l'API :
 * il envoie l'état du formulaire aux deux actions du module, qui répondent en JSON. L'insertion ne fait que remplir
 * les champs du formulaire ; rien n'est enregistré avant le bouton Enregistrer.
 */
define([
    'jquery',
    'underscore',
    'uiElement',
    'uiRegistry',
    'mage/translate'
], function ($, _, Element, registry, $t) {
    'use strict';

    var FIELDS = [
        {code: 'short_description', label: $t('Description courte'), html: false, limit: 150},
        {code: 'description', label: $t('Description'), html: true},
        {code: 'meta_title', label: $t('Titre méta'), html: false, limit: 65},
        {code: 'meta_description', label: $t('Description méta'), html: false, limit: 160},
        {code: 'url_key', label: $t('Clé d\'URL'), html: false},
        {code: 'composition', label: $t('Composition'), html: false},
        {code: 'dimensions', label: $t('Dimensions'), html: false},
        {code: 'entretien', label: $t('Conseils d\'entretien'), html: false}
    ];

    return Element.extend({
        defaults: {
            template: 'MadameAiguille_AiAssistant/assistant',
            analyseUrl: '',
            generateUrl: '',
            productId: 0,
            provider: '',
            step: 'start',
            busy: false,
            waitMessage: '',
            error: '',
            comprehension: '',
            questions: [],
            notes: '',
            proposals: [],
            altTexts: [],
            points: [],
            instruction: '',
            inserted: false,
            previous: null
        },

        initObservable: function () {
            this._super().observe([
                'step', 'busy', 'waitMessage', 'error', 'comprehension', 'questions', 'notes', 'proposals',
                'altTexts', 'points', 'instruction', 'inserted'
            ]);

            return this;
        },

        /** Étape 1 : la fiche et les photos partent pour analyse. */
        analyse: function () {
            this.send(this.analyseUrl, {}, $t('Je lis la fiche et les photos… (10 à 30 secondes)'))
                .done(function (response) {
                    this.comprehension(response.comprehension || '');
                    this.questions(_.map(response.questions || [], function (question) {
                        question.answer = this.observable('');

                        return question;
                    }, this));
                    this.step('questions');
                }.bind(this));
        },

        /** Étape 3 : rédaction ; avec une consigne, retouche du résultat affiché. */
        generate: function (instruction) {
            var data = {
                answers: _.map(this.questions(), function (question) {
                    return {question: question.question, reponse: question.answer()};
                }),
                notes: this.notes()
            };

            if (instruction !== undefined && this.previous) {
                data.previous = this.previous;
                data.instruction = instruction;
            }

            this.send(this.generateUrl, data, $t('Je rédige les textes… (10 à 30 secondes)'))
                .done(function (response) {
                    this.showResult(response);
                    this.instruction('');
                }.bind(this));
        },

        draft: function () {
            this.generate();
        },

        shorter: function () {
            this.generate('plus court');
        },

        warmer: function () {
            this.generate('plus chaleureux');
        },

        regenerate: function () {
            this.generate('régénérer');
        },

        retouchFree: function () {
            if ($.trim(this.instruction()) !== '') {
                this.generate(this.instruction());
            }
        },

        showResult: function (response) {
            var fields = response.fields || {};

            this.previous = {fields: fields, points: response.points || []};
            this.proposals(_.compact(_.map(FIELDS, function (field) {
                var value = fields[field.code];

                return value ? {
                    code: field.code,
                    label: field.label,
                    html: field.html,
                    value: value,
                    length: value.length,
                    tooLong: field.limit ? value.length > field.limit : false,
                    limit: field.limit || 0,
                    checked: this.observable(true)
                } : null;
            }, this)));
            this.altTexts(_.map(response.alt || [], function (alt, index) {
                return {file: alt.file, text: alt.text, number: index + 1, checked: this.observable(true)};
            }, this));
            this.points(response.points || []);
            this.inserted(false);
            this.step('preview');
        },

        /** Remplit les champs cochés du formulaire ; l'enregistrement reste à Céline. */
        insert: function () {
            _.each(this.proposals(), function (proposal) {
                if (proposal.checked()) {
                    this.setField(proposal.code, proposal.value);
                }
            }, this);
            _.each(this.altTexts(), function (alt) {
                if (alt.checked()) {
                    this.setImageLabel(alt.file, alt.text);
                }
            }, this);
            this.inserted(true);
        },

        restart: function () {
            this.previous = null;
            this.error('');
            this.inserted(false);
            this.step('start');
        },

        setField: function (code, value) {
            var field = registry.get('ns = product_form, index = ' + code),
                editor;

            if (!field) {
                return;
            }
            field.value(value);

            // WYSIWYG classique : TinyMCE garde sa propre copie du contenu
            editor = window.tinyMCE && field.wysiwygId ? window.tinyMCE.get(field.wysiwygId) : null;
            if (editor) {
                editor.setContent(value);
            }
        },

        setImageLabel: function (file, text) {
            $('[data-role="image"]').each(function () {
                var $image = $(this),
                    imageData = $image.data('imageData');

                if (imageData && imageData.file === file) {
                    $image.find('input[type="hidden"][name$="[label]"]').val(text);
                    imageData.label = text;
                    imageData['label_default'] = text;
                    $image.closest('[data-role="product-gallery"], .gallery').trigger('updateImageTitle', {
                        imageData: imageData
                    });
                }
            });
        },

        /** État du formulaire, y compris ce qui n'est pas encore enregistré. */
        collect: function () {
            var source = registry.get(this.provider),
                product = source ? source.get('data.product') || {} : {},
                images = [];

            $('[data-role="image"]').each(function () {
                var imageData = $(this).data('imageData');

                if (imageData && imageData.file && !Number(imageData.removed) &&
                    (imageData['media_type'] || 'image') === 'image'
                ) {
                    images.push(imageData.file);
                }
            });

            return {
                product_id: this.productId,
                product: _.pick(product, [
                    'name', 'sku', 'type_id', 'price', 'weight', 'short_description', 'description', 'composition',
                    'dimensions', 'entretien', 'serie_limitee', 'taille_serie', 'category_ids', 'meta_title',
                    'meta_description'
                ]),
                images: images
            };
        },

        send: function (url, data, waitMessage) {
            var deferred = $.Deferred();

            this.error('');
            this.busy(true);
            this.waitMessage(waitMessage);

            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                showLoader: false,
                timeout: 180000,
                data: {
                    'form_key': window.FORM_KEY,
                    payload: JSON.stringify($.extend(this.collect(), data))
                }
            }).done(function (response) {
                if (response && response.ok) {
                    deferred.resolve(response);
                } else {
                    this.error(response && response.message ? response.message :
                        $t('La demande n\'a pas abouti. Votre fiche n\'a pas été modifiée.'));
                }
            }.bind(this)).fail(function () {
                this.error($t('Le serveur ne répond pas. Vérifiez la connexion, puis réessayez. ' +
                    'Votre fiche n\'a pas été modifiée.'));
            }.bind(this)).always(function () {
                this.busy(false);
            }.bind(this));

            return deferred.promise();
        }
    });
});

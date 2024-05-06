FROM michaelmcandrew/civicrm:${CIVICRM_IMAGE_TAG:-5-drupal-php8.1}
LABEL authors="Marc Michalsky"

ENTRYPOINT ["top", "-b"]

# Install required extensions
WORKDIR "/var/www/html/sites/default/files/civicrm/ext"
RUN curl -L https://github.com/systopia/de.systopia.xcm/releases/download/1.12.0/de.systopia.xcm-${XCM_VERSION:1.12.0}.tar.gz -o de.systopia.xcm.tar.gz
RUN tar -xzf de.systopia.xcm.tar.gz

ENTRYPOINT [ "docker-civicrm-entrypoint" ]
CMD ["apache2-foreground"]

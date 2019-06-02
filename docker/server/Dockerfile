# =============================================================================
#
# CentOS-7, Apache 2.4, PHP 7.1, MYSQL, DB2
#
# =============================================================================

FROM centos:centos7

MAINTAINER AFN <report@alifuatnumanoglu.com>


# -----------------------------------------------------------------------------
# Environment Parameters
# -----------------------------------------------------------------------------
ENV TIMEZONE                      UTC
ENV MAX_EXECUTION_TIME            30
ENV MAX_FILE_UPLOADS              20
ENV MAX_INPUT_VARS                1000
ENV POST_MAX_SIZE                 8M
ENV UPLOAD_MAX_FILESIZE           2M
ENV MEMORY_LIMIT                  512M
ENV DISPLAY_ERRORS                1
ENV ERROR_REPORTING               -1
ENV LOG_ERRORS                    true
ENV ERROR_LOG                     /var/www/html/error_log
ENV XDEBUG_DEFAULT_ENABLE         1
ENV XDEBUG_REMOTE_CONNECT_BACK    1
ENV XDEBUG_REMOTE_ENABLE          1
ENV XDEBUG_REMOTE_HANDLER         dbgp
ENV XDEBUG_REMOTE_PORT            9000
ENV XDEBUG_REMOTE_AUTOSTART       1
ENV XDEBUG_PROFILER_ENABLE        1


# -----------------------------------------------------------------------------
# Import the RPM GPG keys to prevent warnings and Add EPEL Repository
# -----------------------------------------------------------------------------
RUN rpm --import http://mirror.centos.org/centos/RPM-GPG-KEY-CentOS-7 \
    && rpm --import http://dl.fedoraproject.org/pub/epel/RPM-GPG-KEY-EPEL-7 \
    && rpm -Uvh http://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm \
    && rpm -Uvh https://mirror.webtatic.com/yum/el7/webtatic-release.rpm


# -----------------------------------------------------------------------------
# PACKAGES (Apache + PHP ...)
# -----------------------------------------------------------------------------
RUN yum -y install \
    mod_php71w \
    php71w-bcmath \
    php71w-common \
    php71w-cli \
    php71w-xml \
    php71w-gd \
    php71w-pecl-imagick \
    php71w-imap \
    inotify-tools.x86_64 \
    php71w-intl \
    php71w-mbstring \
    php71w-mcrypt \
    php71w-mysql \
    php71w-pdo \
    php71w-process \
    php71w-soap \
    php71w-xml \
    php71w-xmlrpc \
    php71w-ldap \
    php71w-pear \
    php71w-pecl-xdebug \
    php71w-pecl-memcached \
    php71w-pecl-apcu \
    curl wget \
    vim \
    nano \
    tar \
    screen \
    crontabs \
    perl \
    unzip \
    initscripts \
    systemd \
    sudo \
    gcc \
    gcc-c++ \
    httpd \
    mysql-devel \
    mysql-libs \
    mod_ssl \
    php-pear-MDB2-Driver-mysqli \
    msmtp \
    && yum -y update bash \
    && rm -rf /var/cache/yum/* \
    && yum clean all

# -----------------------------------------------------------------------------
# UTC Timezone & Networking
# -----------------------------------------------------------------------------
RUN ln -sf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime \
    && echo "NETWORKING=yes" > /etc/sysconfig/network


# -----------------------------------------------------------------------------
# Global Apache configuration changes
# Disable Apache directory indexes
# Disable Apache language based content negotiation
# Disable all Apache modules and enable the minimum
# Enable ServerStatus access via /_httpdstatus to local client
# Apache tuning
# -----------------------------------------------------------------------------
RUN sed -i \
    -e 's~^ServerSignature On$~ServerSignature Off~g' \
    -e 's~^ServerTokens OS$~ServerTokens Prod~g' \
    -e 's~^#ExtendedStatus On$~ExtendedStatus On~g' \
    -e 's~^DirectoryIndex \(.*\)$~DirectoryIndex \1 index.php~g' \
    -e 's~^NameVirtualHost \(.*\)$~#NameVirtualHost \1~g' \
    -e 's~^IndexOptions \(.*\)$~#IndexOptions \1~g' \
    -e 's~^IndexIgnore \(.*\)$~#IndexIgnore \1~g' \
    -e 's~^AddIconByEncoding \(.*\)$~#AddIconByEncoding \1~g' \
    -e 's~^AddIconByType \(.*\)$~#AddIconByType \1~g' \
    -e 's~^AddIcon \(.*\)$~#AddIcon \1~g' \
    -e 's~^DefaultIcon \(.*\)$~#DefaultIcon \1~g' \
    -e 's~^ReadmeName \(.*\)$~#ReadmeName \1~g' \
    -e 's~^HeaderName \(.*\)$~#HeaderName \1~g' \
    -e 's~^LanguagePriority \(.*\)$~#LanguagePriority \1~g' \
    -e 's~^ForceLanguagePriority \(.*\)$~#ForceLanguagePriority \1~g' \
    -e 's~^AddLanguage \(.*\)$~#AddLanguage \1~g' \
    -e 's~^\(LoadModule .*\)$~#\1~g' \
    -e 's~^\(#LoadModule version_module modules/mod_version.so\)$~\1\n#LoadModule reqtimeout_module modules/mod_reqtimeout.so~g' \
    -e 's~^#LoadModule mime_module ~LoadModule mime_module ~g' \
    -e 's~^#LoadModule log_config_module ~LoadModule log_config_module ~g' \
    -e 's~^#LoadModule setenvif_module ~LoadModule setenvif_module ~g' \
    -e 's~^#LoadModule status_module ~LoadModule status_module ~g' \
    -e 's~^#LoadModule authz_host_module ~LoadModule authz_host_module ~g' \
    -e 's~^#LoadModule dir_module ~LoadModule dir_module ~g' \
    -e 's~^#LoadModule alias_module ~LoadModule alias_module ~g' \
    -e 's~^#LoadModule rewrite_module ~LoadModule rewrite_module ~g' \
    -e 's~^#LoadModule expires_module ~LoadModule expires_module ~g' \
    -e 's~^#LoadModule deflate_module ~LoadModule deflate_module ~g' \
    -e 's~^#LoadModule headers_module ~LoadModule headers_module ~g' \
    -e 's~^#LoadModule alias_module ~LoadModule alias_module ~g' \
    -e '/#<Location \/server-status>/,/#<\/Location>/ s~^#~~' \
    -e '/<Location \/server-status>/,/<\/Location>/ s~Allow from .example.com~Allow from localhost 127.0.0.1~' \
    -e 's~^StartServers \(.*\)$~StartServers 3~g' \
    -e 's~^MinSpareServers \(.*\)$~MinSpareServers 3~g' \
    -e 's~^MaxSpareServers \(.*\)$~MaxSpareServers 3~g' \
    -e 's~^ServerLimit \(.*\)$~ServerLimit 10~g' \
    -e 's~^MaxClients \(.*\)$~MaxClients 10~g' \
    -e 's~^MaxRequestsPerChild \(.*\)$~MaxRequestsPerChild 1000~g' \
    /etc/httpd/conf/httpd.conf

RUN sed -i \
    's/AllowOverride None/AllowOverride All/' \
    /etc/httpd/conf/httpd.conf

# -----------------------------------------------------------------------------
# Global PHP configuration changes
# -----------------------------------------------------------------------------
RUN sed -i \
    -e 's~^;date.timezone =$~date.timezone = ${TIMEZONE}~g' \
    -e 's~^;user_ini.filename =$~user_ini.filename =~g' \
    -e 's~^;sendmail_path = /usr/sbin/sendmail -t -i$~sendmail_path = /usr/bin/msmtp -C /etc/msmtprc -t -i~g' \
    -e 's~^;memory_limit =$~memory_limit = ${MEMORY_LIMIT}~g' \
    -e 's~^;upload_max_filesize =$~upload_max_filesize = ${UPLOAD_MAX_FILESIZE}~g' \
    -e 's~^;max_file_uploads =$~max_file_uploads = ${MAX_FILE_UPLOADS}~g' \
    -e 's~^;post_max_size =$~post_max_size = ${POST_MAX_SIZE}~g' \
    -e 's~^;max_execution_time =$~max_execution_time = ${MAX_EXECUTION_TIME}~g' \
    -e 's~^;max_input_vars =$~max_input_vars = ${MAX_INPUT_VARS}~g' \
    -e 's~^;display_errors =$~display_errors = ${DISPLAY_ERRORS}~g' \
    -e 's~^;error_reporting =$~error_reporting = ${ERROR_REPORTING}~g' \
    -e 's~^;log_errors =$~log_errors = ${LOG_ERRORS}~g' \
    /etc/php.ini

RUN echo -e "\nerror_log = ${ERROR_LOG}" >> /etc/php.ini

RUN echo -e \
    "xdebug.default_enable=${XDEBUG_DEFAULT_ENABLE}\n" \
    "xdebug.remote_connect_back=${XDEBUG_REMOTE_CONNECT_BACK}\n" \
    "xdebug.remote_enable=${XDEBUG_REMOTE_ENABLE}\n" \
    "xdebug.remote_handler=${XDEBUG_REMOTE_HANDLER}\n" \
    "xdebug.remote_port=${XDEBUG_REMOTE_PORT}\n" \
    "xdebug.remote_autostart=${XDEBUG_REMOTE_AUTOSTART}\n" \
    "xdebug.profiler_enable=${XDEBUG_PROFILER_ENABLE}" \
    >> /etc/php.d/xdebug.ini


# -----------------------------------------------------------------------------
# Add msmtp example configuration(MAIL)
# -----------------------------------------------------------------------------
RUN curl http://msmtp.sourceforge.net/doc/msmtprc.txt -o /etc/msmtprc


# -----------------------------------------------------------------------------
# Install Composer
# -----------------------------------------------------------------------------
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer


# -----------------------------------------------------------------------------
# Install Bowerphp
# -----------------------------------------------------------------------------
COPY ./bowerphp.phar /usr/bin/


# -----------------------------------------------------------------------------
# Centos systemd configuration
# -----------------------------------------------------------------------------
RUN (cd /lib/systemd/system/sysinit.target.wants/; for i in *; do [ $i == systemd-tmpfiles-setup.service ] || rm -f $i; done); \
    rm -f /lib/systemd/system/multi-user.target.wants/*;\
    rm -f /etc/systemd/system/*.wants/*;\
    rm -f /lib/systemd/system/local-fs.target.wants/*; \
    rm -f /lib/systemd/system/sockets.target.wants/*udev*; \
    rm -f /lib/systemd/system/sockets.target.wants/*initctl*; \
    rm -f /lib/systemd/system/basic.target.wants/*;\
    rm -f /lib/systemd/system/anaconda.target.wants/*;

VOLUME [ "/sys/fs/cgroup" ]

CMD ["/usr/sbin/init"]


# -----------------------------------------------------------------------------
# Copy files into place
# -----------------------------------------------------------------------------
# ADD


# -----------------------------------------------------------------------------
# Purge
# -----------------------------------------------------------------------------
RUN rm -rf /sbin/sln \
    ; rm -rf /usr/{{lib,share}/locale,share/{man,doc,info,gnome/help,cracklib,il8n},{lib,lib64}/gconv,bin/localedef,sbin/build-locale-archive} \
    ; rm -rf /var/cache/{ldconfig,yum}/*


# -----------------------------------------------------------------------------
# Composer download some libraries
# -----------------------------------------------------------------------------
RUN composer require --dev phpunit/phpunit ^7


# -----------------------------------------------------------------------------
# END
# -----------------------------------------------------------------------------
EXPOSE 80 443 3306

CMD /usr/sbin/httpd -c "ErrorLog /dev/stdout" -DFOREGROUND

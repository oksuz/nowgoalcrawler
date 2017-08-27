FROM ubuntu:16.04

MAINTAINER Yunus Oksuz <yunusoksuz@gmail.com>
RUN apt-get update -y
RUN apt-get install -y tzdata locales cron php-mcrypt php-mysql php php-curl supervisor

# configure timezone
RUN echo "Europe/Istanbul" > /etc/timezone
RUN rm -f /etc/localtime
RUN dpkg-reconfigure -f noninteractive tzdata

RUN mkdir -p /opt/app
COPY out/* /opt/app/

RUN mkdir /opt/app/tmp
RUN chmod 0777 /opt/app/tmp
RUN chmod 0777 /opt/app/app.log
RUN chmod +x /opt/app/run

ADD out/supervisor-nowgoal.conf /etc/supervisor/conf.d/supervisor-nowgoal.conf

WORKDIR /opt/app

CMD ["/usr/bin/supervisord", "-n"]

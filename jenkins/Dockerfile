FROM jenkins/jenkins:lts

USER root

# Install dependencies for Flutter
RUN apt-get update && apt-get install -y \
    curl git unzip xz-utils zip libglu1-mesa && \
    rm -rf /var/lib/apt/lists/*

# Install Flutter
RUN git clone https://github.com/flutter/flutter.git /opt/flutter && \
    /opt/flutter/bin/flutter doctor

# Add Flutter to PATH
ENV PATH="$PATH:/opt/flutter/bin"


RUN #apt-get install vim -y

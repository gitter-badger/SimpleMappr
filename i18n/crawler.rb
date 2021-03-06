require 'find'

system(`rm -f messages.po`)
po_file = File.open("messages.po", 'a')

excludes = [".git", ".DS_Store"]
exclude_files = []
strings = []
lines = []
output = ""
basedir = ARGV[0] || "."
Find.find(basedir) do |path|
  if FileTest.directory?(path)
    if excludes.include?(File.basename(path))
      Find.prune
    else
      next
    end
  else
    filename = File.basename(path)
    if filename =~ /\.php/ and not exclude_files.include?(filename)
      cleaned = path.sub("./", "")
      contents = File.read(path)
      if contents =~ /sprintf\(_\(([^\)]+)\), ([^\)]+)\)/
        counter = 1
        File.open(path, "r") do |infile|
          while (line = infile.gets)
            line.gsub!("\\\"", "{QUOTE}")
            line.gsub(/sprintf\(_\("([^"]+)"(, "[^"]+")?\), ([^\)]+)\)/) do
              text = $1.gsub("{QUOTE}", "\\\"")
              unless strings.include?(text)
                output << '#: '+cleaned+':'+counter.to_s+"\n"
                output << '#, php-format'+"\n"
                output << 'msgid "'+text+'"'+"\n"
                output << 'msgstr ""'+"\n\n"
                strings << text
                lines << cleaned+":"+counter.to_s
              else
                output = output.gsub("#, php-format\nmsgid \""+text+"\"\nmsgstr \"\"\n\n", 
                                     "#: "+cleaned+":"+counter.to_s+"\n#, php-format\nmsgid \""+text+"\"\nmsgstr \"\"\n\n")
              end
            end
            counter = counter + 1
          end
        end
      end
      if contents =~ /_\((.*?)\)/
        counter = 1
        File.open(path, "r") do |infile|
          while (line = infile.gets)
            line.gsub!("\\\"", "{QUOTE}")
            line.gsub(/_\("([^"]+)"(, "[^"]+")?\)/) do
              text = $1.gsub("{QUOTE}", "\\\"")
              unless strings.include?(text)
                output << '#: '+cleaned+':'+counter.to_s+"\n"
                output << 'msgid "'+text+'"'+"\n"
                output << 'msgstr ""'+"\n\n"
                strings << text
              else
                unless lines.include?(cleaned+":"+counter.to_s)
                  output = output.gsub("msgid \""+text+"\"\nmsgstr \"\"\n\n", 
                                       "#: "+cleaned+":"+counter.to_s+"\nmsgid \""+text+"\"\nmsgstr \"\"\n\n")
                end
              end
            end
            counter = counter + 1
          end
        end
      end
    end
  end
end

po_file.puts '# SimpleMappr Translation File.'
po_file.puts '# Copyright (C) 2011 David P. Shorthouse'
po_file.puts '# This file is distributed under the same license as the SimpleMappr application.'
po_file.puts '# David P. Shorthouse <(snipped)>, 2011.'
po_file.puts '#'
po_file.puts '#, fuzzy'
po_file.puts 'msgid ""'
po_file.puts 'msgstr ""'
po_file.puts '"Project-Id-Version: SimpleMappr v1.5\n"'
po_file.puts '"Report-Msgid-Bugs-To: (snipped)\n"'
po_file.puts '"POT-Creation-Date: 2011-11-01 00:29-0500\n"'
po_file.puts '"PO-Revision-Date: '+Time.now.strftime("%Y-%m-%d %H:%M")+'-0500\n"'
po_file.puts '"Last-Translator: David Shorthouse <(snipped)>\n"'
po_file.puts '"Language-Team: English (en) <(snipped)>\n"'
po_file.puts '"Language: \n"'
po_file.puts '"MIME-Version: 1.0\n"'
po_file.puts '"Content-Type: text/plain; charset=UTF-8\n"'
po_file.puts '"Content-Transfer-Encoding: 8bit\n"'
po_file.puts ''
po_file.puts output